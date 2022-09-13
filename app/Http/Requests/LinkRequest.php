<?php

namespace App\Http\Requests;

use App\Models\Link;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Link|null link
 */
class LinkRequest extends FormRequest
{
    private Client $client;
    public bool $manyLinksProvided;
    private string $prefix = 'links';
    private const URL_VALIDATION_REGEX = '/^((https?):\/\/)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/=]*)$/i';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueRule = Rule::unique('links', 'long_url');
        if ($this->link) {
            $uniqueRule->ignoreModel($this->link);
        }

        $prefix = $this->manyLinksProvided ? "$this->prefix.*." : '';
        $rules = [
            "{$prefix}long_url" => [
                'bail',
                'required',
                'string',
                'max:2048',
                $uniqueRule,
                'regex:'.self::URL_VALIDATION_REGEX,
                //todo figure out a way to only check it last to not waste response time when it's not needed
                function ($attribute, $value, $fail) {
                    try {
                        $this->client->get($value);
                    } catch (GuzzleException $exception) {
                        $shortMessage = preg_replace('/(curl error .*) \(see .*/i', '$1', $exception->getMessage());
                        $fail("The $attribute failed to load: '$shortMessage'");
                    }
                },
            ],
            "{$prefix}title" => 'string|max:255',
            "{$prefix}tags" => 'array'
        ];

        if ($this->manyLinksProvided) {
            $rules = array_merge([$this->prefix => 'bail|array|max:5'], $rules);
        }
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->client = new Client();
        $this->manyLinksProvided = is_array(json_decode($this->getContent(), false, 512, JSON_THROW_ON_ERROR));
        if ($this->manyLinksProvided) {
            $data = $this->all();
            $this->merge(['links' => $data]);
        }

        $this->getValidatorInstance()->after(fn() => $this->getInputSource()?->remove('links'));
    }
}

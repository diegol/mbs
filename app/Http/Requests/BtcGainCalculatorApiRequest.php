<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class BtcGainCalculatorApiRequest
 *
 * Use to validate the request for Api for the BTCGainCalculator
 *
 *
 * @package App\Http\Requests
 */
class BtcGainCalculatorApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'months' => 'required|integer|max:18',
            'amount' => 'required|integer|max:10000',
            'currency' => 'required|in:nzd,aud,usd',
        ];
    }

    /**
     * Custom Failed Response
     *
     * Overrides the Illuminate\Foundation\Http\FormRequest
     * response function to stop it from auto redirecting
     * and applies a API custom response format.
     *
     * @param array $errors
     * @return JsonResponse
     */
    protected function failedValidation(Validator $validator) {

        throw new HttpResponseException(
            response()
                ->json(
                    [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'errors' => $validator->errors(),
                    ]
                ),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Get all of the input and files for the request.
     *
     * @param  array|mixed  $keys
     * @return array
     */
    public function all($keys = null)
    {
        $results = parent::all($keys);

        $results['months']   = $this->route('months');
        $results['currency'] = $this->route('currency');


        return $results;
    }

}

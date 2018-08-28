<?php

namespace Botble\Contact\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ContactRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @author QuocDung Dang
     */
    public function rules()
    {
        if (setting('enable_captcha')) {
            return [
                'name' => 'required',
                'email' => 'required|email',
                'content' => 'required',
                'g-recaptcha-response' => 'required|captcha',
            ];
        } else {
            return [
                'name' => 'required',
                'email' => 'required|email',
                'content' => 'required',
            ];
        }
    }

    /**
     * @return array
     * @author QuocDung Dang
     */
    public function messages()
    {
        return [
            'name.required' => trans('plugins.contact::contact.name.required'),
            'email.required' => trans('plugins.contact::contact.email.required'),
            'email.email' => trans('plugins.contact::contact.email.email'),
            'content.required' => trans('plugins.contact::contact.content.required'),
            'g-recaptcha-response.required' => trans('plugins.contact::contact.g-recaptcha-response.required'),
            'g-recaptcha-response.captcha' => trans('plugins.contact::contact.g-recaptcha-response.captcha'),
        ];
    }
}
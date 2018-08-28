<?php

namespace Botble\SimpleSlider\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SimpleSliderRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @author QuocDung Dang
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'key' => 'required',
        ];
    }
}

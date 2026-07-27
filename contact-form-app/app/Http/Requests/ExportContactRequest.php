<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search_name' => 'nullable|string|max:255',
            'search_gender' => 'nullable|in:1,2,3',
            'search_email' => 'nullable|email|max:255',
            'search_phone' => 'nullable|string|max:15',
            'search_address' => 'nullable|string|max:255',
            'search_category' => 'nullable|integer|exists:categories,id',
            'sort' => 'nullable|in:asc,desc',
        ];
    }

    public function messages()
    {
        return [
            'search_name.string' => '氏名は文字列でなければなりません。',
            'search_gender.in' => '性別は有効な選択肢でなければなりません。',
            'search_email.email' => 'メールアドレスの形式が正しくありません。',
            'search_phone.string' => '電話番号は文字列でなければなりません。',
            'search_category.exists' => '指定されたカテゴリは存在しません。',
            'sort.in' => 'ソートオプションは有効な選択肢でなければなりません。',
        ];
    }
}

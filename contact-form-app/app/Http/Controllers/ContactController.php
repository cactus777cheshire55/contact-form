<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Models\Contact; // Assuming you have a Contact model
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function export(ExportContactRequest $request)
    {
        // Retrieve query parameters for filtering
        $filters = $request->validated();

        // Build the query based on filters
        $query = Contact::query();

        if (! empty($filters['name'])) {
            $query->where('first_name', 'like', '%'.$filters['name'].'%')
                ->orWhere('last_name', 'like', '%'.$filters['name'].'%');
        }

        if (! empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (! empty($filters['email'])) {
            $query->where('email', 'like', '%'.$filters['email'].'%');
        }

        // Default to all records if no filters are applied
        $contacts = $query->orderBy('created_at', 'desc')->get();

        // Prepare CSV headers
        $headers = [
            'ID',
            '氏名',
            '性別',
            'メール',
            '電話',
            '住所',
            '建物',
            'カテゴリ',
            '内容',
            '作成日時',
        ];

        // Create a response with CSV content
        $csvFileName = 'contacts_export_'.date('Ymd_His').'.csv';
        $handle = fopen('php://output', 'w');

        // Output BOM for UTF-8
        fwrite($handle, "\xEF\xBB\xBF");

        // Output headers
        fputcsv($handle, $headers);

        // Output data
        foreach ($contacts as $contact) {
            fputcsv($handle, [
                $contact->id,
                $contact->first_name.' '.$contact->last_name,
                $this->getGenderString($contact->gender),
                $contact->email,
                $contact->tel,
                $contact->address,
                $contact->building,
                $this->getCategoryString($contact->category_id), // Assuming you have a method to get category name
                $contact->detail,
                $contact->created_at,
            ]);
        }

        fclose($handle);

        return response()->stream(function () use ($handle) {
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$csvFileName.'"',
        ]);
    }

    private function getGenderString($gender)
    {
        switch ($gender) {
            case 1:
                return '男性';
            case 2:
                return '女性';
            case 3:
                return 'その他';
            default:
                return '';
        }
    }

    private function getCategoryString($categoryId)
    {
        // Assuming you have a Category model to get the category name
        return optional(Category::find($categoryId))->content ?? '';
    }
}

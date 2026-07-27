<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw('CONCAT(last_name, first_name) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('CONCAT(first_name, last_name) LIKE ?', ["%{$keyword}%"]);
            });
        }

        if (($gender = $request->query('gender')) !== null && $gender !== '0' && $gender !== 0) {
            $query->where('gender', $gender);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($date = $request->query('date')) {
            $query->whereDate('created_at', $date);
        }

        $categories = Category::all();
        $tags = Tag::all();
        $contacts = $query->latest()->paginate(7);

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin')->with('success', 'お問い合わせを削除しました。');
    }

    public function export(ExportContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        if ($keyword = $request->query('keyword')) {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw('CONCAT(last_name, first_name) LIKE ?', ["%{$keyword}%"])
                    ->orWhereRaw('CONCAT(first_name, last_name) LIKE ?', ["%{$keyword}%"]);
            });
        }

        if (($gender = $request->query('gender')) !== null && $gender !== '0' && $gender !== 0) {
            $query->where('gender', $gender);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($date = $request->query('date')) {
            $query->whereDate('created_at', $date);
        }

        $contacts = $query->latest()->get();

        $filename = 'contacts_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];

        $callback = function () use ($contacts, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $genderLabels = [1 => '男性', 2 => '女性', 3 => 'その他'];

            foreach ($contacts as $contact) {
                $tags = $contact->tags->pluck('name')->join(', ');
                fputcsv($file, [
                    $contact->id,
                    $contact->first_name.' '.$contact->last_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content ?? '',
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

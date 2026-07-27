<!-- This file defines the contact form view, including an export button for exporting contact data as a CSV file. -->

<div class="grid grid-cols-3 gap-8 mb-4">
    <div class="col-span-1 flex items-center">
        <label class="text-sm text-[#6b5744]">
            エクスポート
        </label>
    </div>
    <div class="col-span-2">
        <form action="{{ route('contacts.export') }}" method="GET" class="flex items-center">
            <input type="text" name="search" placeholder="検索条件" class="flex-1 px-4 py-3 bg-[#f5f5f5] border-0 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-300" />
            <button type="submit" class="ml-4 px-4 py-2 bg-[#6b5744] text-white rounded hover:bg-[#5a4a3a]">
                エクスポート
            </button>
        </form>
    </div>
</div>
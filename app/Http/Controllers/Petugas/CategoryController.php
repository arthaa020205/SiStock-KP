<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        return view('petugas.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('petugas.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'is_active.required' => 'Status kategori wajib dipilih.',
        ]);

        $category = Category::create($validated);

        ActivityLogger::log(
            'category_created',
            'Menambahkan kategori: ' . $category->name,
            $category,
            [
                'name' => $category->name,
                'description' => $category->description,
                'is_active' => $category->is_active,
            ]
        );

        return redirect()
            ->route('petugas.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('petugas.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'is_active.required' => 'Status kategori wajib dipilih.',
        ]);

        $oldData = $category->only([
            'name',
            'description',
            'is_active',
        ]);

        $category->update($validated);

        ActivityLogger::log(
            'category_updated',
            'Memperbarui kategori: ' . $category->name,
            $category,
            [
                'old' => $oldData,
                'new' => $category->only([
                    'name',
                    'description',
                    'is_active',
                ]),
            ]
        );

        return redirect()
            ->route('petugas.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('petugas.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena sudah digunakan oleh barang.');
        }

        $categoryData = [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'is_active' => $category->is_active,
        ];

        $categoryName = $category->name;
        $categoryId = $category->id;

        $category->delete();

        ActivityLogger::log(
            'category_deleted',
            'Menghapus kategori: ' . $categoryName,
            null,
            $categoryData + [
                'deleted_category_id' => $categoryId,
            ]
        );

        return redirect()
            ->route('petugas.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}

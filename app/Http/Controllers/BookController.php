<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
public function listAllBooks(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized. Only admins can view all books.'], 403);
    }

    $books = Book::with(['user', 'category'])->get();

    return response()->json([
        'message' => 'List of all books',
        'data' => $books
    ]);
}



public function viewBook($id)
{
    // Find the book by ID, only if approved, with category and user loaded
    $book = Book::with('category', 'user')
                ->where('id', $id)
                ->where('status', 'approved')
                ->first();

    if (!$book) {
        return response()->json(['message' => 'Book not found or not approved'], 404);
    }

    return response()->json([
        'message' => 'Book detail',
        'book' => $book
    ]);
}


    // List all books
    public function listBooks(Request $request)
    {
        $books = Book::with('category')->where('status', 'approved')->get();
        return response()->json(['books' => $books]);
    }

    // Add book (seller only)
    public function addBook(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can add books.'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'author' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'cover_image' => 'nullable|image',
            'category_name' => 'required|string',
        ]);

        $category = Category::firstOrCreate(['name' => $request->category_name]);

        $book = new Book();
        $book->name = $request->name;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->price = $request->price;
        $book->category_id = $category->id;
        $book->seller_id = $user->id;
        $book->status = 'pending'; // Waiting for admin approval
        $book->save();

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('book_covers', 'public');
            $book->cover_image = $path;
            $book->save();
        }

        return response()->json(['message' => 'Book submitted for approval', 'book' => $book]);
    }

    // View single book
//     public function viewBook(Book $book)
// {
//     $book->load('category', 'user'); // Load category and seller info

//     return response()->json([
//         'message' => 'Book detail',
//         'book' => $book
//     ]);
// }


    // Admin approves a book
    public function approveBook(Request $request, Book $book)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $book->status = 'approved';
        $book->save();

        return response()->json(['message' => 'Book approved successfully']);
    }

    // Admin rejects a book with a note
public function rejectBook(Request $request, Book $book)
{
    $admin = $request->user(); // Or Auth::user()

    if ($admin && $admin->role === 'admin') {
        // Require the rejection note
        $validated = $request->validate([
            'reject_note' => 'required|string|max:500',
        ]);

        $book->status = 'disapproved';
        $book->reject_note = $validated['reject_note'];
        $book->save();

        return response()->json([
            'message' => 'Book rejected successfully with note',
            'book' => $book
        ]);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
}







    // Delete book (seller only)
    public function deleteBookById(Request $request, $id)
    {
        $user = $request->user();
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found.'], 404);
        }

        if ($user->role !== 'seller' || $book->seller_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to delete this book.'], 403);
        }

        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully.']);
    }

  // Update book (seller only)
public function updateBook(Request $request, $id)
{
    $user = $request->user();

    if (strtolower($user->role) !== 'seller') {
        return response()->json(['message' => 'Only sellers can update books.'], 403);
    }

    $book = Book::find($id);

    if (!$book) {
        return response()->json(['message' => 'Book not found.'], 404);
    }

    if ($book->seller_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized to update this book.'], 403);
    }

    $validated = $request->validate([
        'name' => 'sometimes|required|string',
        'author' => 'sometimes|required|string',
        'description' => 'nullable|string',
        'price' => 'sometimes|required|numeric',
        'cover_image' => 'nullable|image',
        'category_name' => 'sometimes|required|string|exists:categories,name',
    ]);

    // Update fields if provided
    if (isset($validated['name'])) $book->name = $validated['name'];
    if (isset($validated['author'])) $book->author = $validated['author'];
    if (isset($validated['description'])) $book->description = $validated['description'];
    if (isset($validated['price'])) $book->price = $validated['price'];

    // Update category if provided
    if (isset($validated['category_name'])) {
        $category = Category::where('name', $validated['category_name'])->first();
        if ($category) {
            $book->category_id = $category->id;
        } else {
            return response()->json(['message' => 'Category not found.'], 404);
        }
    }

    // Handle cover image upload
    if ($request->hasFile('cover_image')) {
        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $path = $request->file('cover_image')->store('book_covers', 'public');
        $book->cover_image = $path;
    }

    // Reset status to pending for re-approval
    $book->status = 'pending';
    $book->rejection_note = null;

    if ($book->save()) {
        $book->load('category');
        return response()->json([
            'message' => 'Book updated and re-submitted for approval.',
            'book' => $book
        ]);
    }

    return response()->json(['message' => 'Failed to update the book.'], 500);
}


    // Search books by name and optional category
    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query = Book::with('category')->where('status', 'approved');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $query->where('name', 'like', $request->name . '%');
        $books = $query->get();

        return response()->json(['books' => $books]);
    }

    // Seller views their own books
    public function myBooks(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can view their own posts.'], 403);
        }

        $books = Book::with('category')
            ->where('seller_id', $user->id)
            ->get();

        return response()->json(['books' => $books]);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Models\Author;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {

    return view('welcome');
});


Route::get('/test-queries', function () {
    // 1. Простой запрос (быстрый)
    $simpleQuery = Author::all();

    // 2. Запрос с отношениями (может быть медленнее)
    $eagerLoading = Author::with('books')->get();

    // 3. Сложный запрос с несколькими объединениями (может быть медленным)
    $complexJoin = DB::table('authors')
        ->join('books', 'authors.id', '=', 'books.author_id')
        ->join('reviews', 'books.id', '=', 'reviews.book_id')
        ->select('authors.name', 'books.title', 'reviews.rating')
        ->get();

    // 4. Запрос с подзапросом (потенциально медленный)
    $subQuery = Author::whereHas('books', function ($query) {
        $query->where('pages', '>', 500);
    })->get();

    // 5. Запрос с агрегацией (может быть медленным на больших объемах данных)
    $aggregation = Book::groupBy('author_id')
        ->selectRaw('author_id, AVG(pages) as average_pages')
        ->having('average_pages', '>', 300)
        ->get();

    // 6. Запрос с сортировкой по связанной таблице (может быть очень медленным)
    $sortByRelation = Author::withCount('books')
        ->orderBy('books_count', 'desc')
        ->get();

    // 7. Запрос с использованием like (может быть медленным без индекса)
    $likeQuery = Book::where('title', 'like', '%adventure%')->get();

    // 8. Запрос с множественными условиями (может быть медленным)
    $multiCondition = Book::where('pages', '>', 200)
        ->where('created_at', '>', now()->subMonths(6))
        ->whereHas('reviews', function ($query) {
            $query->where('rating', '>', 4);
        })
        ->get();

    // 9. Запрос с использованием raw SQL (может быть быстрым или медленным, зависит от SQL)
    $rawSql = DB::select(DB::raw('SELECT * FROM books WHERE pages > 500'));

    // 10. Запрос с использованием union (может быть медленным)
    $union = Book::where('pages', '<', 100)
        ->union(Book::where('pages', '>', 500))
        ->get();

    // 11. Запрос с использованием whereIn с большим количеством значений (может быть медленным)
    $largeWhereIn = Book::whereIn('id', range(1, 1000))->get();

    // 12. Запрос с использованием distinct (может быть медленным на больших таблицах)
    $distinct = Review::select('book_id')->distinct()->get();

    // 13. Запрос с использованием having без group by (может быть медленным)
    $havingWithoutGroup = Book::select('author_id')
        ->having(DB::raw('COUNT(*)'), '>', 5)
        ->get();

    // 14. Запрос с использованием подзапроса в select (может быть медленным)
    $subQueryInSelect = Author::select('*', DB::raw('(SELECT AVG(rating) FROM reviews WHERE reviews.book_id IN (SELECT id FROM books WHERE books.author_id = authors.id)) as avg_rating'))
        ->get();

    // 15. Запрос с использованием функции в where (может быть медленным)
    $functionInWhere = Book::whereRaw('LOWER(title) = ?', [strtolower('Some Title')])->get();

    // 16. Запрос с использованием full text search (может быть быстрым с правильным индексом, медленным без него)
    $fullTextSearch = Book::whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', ['adventure'])->get();

    // 17. Запрос с использованием сложной агрегации (может быть очень медленным)
    $complexAggregation = DB::table('books')
        ->join('reviews', 'books.id', '=', 'reviews.book_id')
        ->select('books.author_id', DB::raw('AVG(reviews.rating) as avg_rating, COUNT(DISTINCT books.id) as book_count'))
        ->groupBy('books.author_id')
        ->havingRaw('AVG(reviews.rating) > ?', [4])
        ->orderBy('avg_rating', 'desc')
        ->get();

    // 18. Запрос с использованием cross join (может быть очень медленным на больших таблицах)
    $crossJoin = DB::table('authors')
        ->crossJoin('books')
        ->select('authors.name', 'books.title')
        ->limit(100)
        ->get();

    // 19. Запрос с использованием оконных функций (может быть медленным)
    $windowFunction = DB::table('books')
        ->select('title', 'pages',
            DB::raw('AVG(pages) OVER (PARTITION BY author_id) as avg_pages_by_author')
        )
        ->orderBy('author_id')
        ->get();

    // 20. Запрос с использованием рекурсивного CTE (может быть медленным)
    $recursiveCTE = DB::select("
        WITH RECURSIVE category_path (id, name, path) AS
        (
          SELECT id, name, name as path
          FROM categories
          WHERE parent_id IS NULL
          UNION ALL
          SELECT c.id, c.name, CONCAT(cp.path, ' > ', c.name)
          FROM category_path AS cp JOIN categories AS c
            ON cp.id = c.parent_id
        )
        SELECT * FROM category_path
        ORDER BY path;
    ");

    return "Queries executed successfully";
});

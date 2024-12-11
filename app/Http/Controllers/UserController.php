<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Utils\JsonFormatter;
use Illuminate\Database\UniqueConstraintViolationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show()
    {
        $users = User::paginate(30);
        return view("users.index", ["users" => $users]);
    }

    public function showCreate()
    {
        return view("users.create");
    }

    public function showEdit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::all();
            return JsonFormatter::successFormatJson("Users retrieved successfully", 0, $users);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, User::$rules);

            $user = new User();

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->second_lastname = $request->second_lastname;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);

            $user->save();

            return JsonFormatter::successFormatJson("User created successfully", 0, $user);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (UniqueConstraintViolationException $e) {
            return JsonFormatter::errorFormatJson('Email already exists', -4, null);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }


    public function formCreate(Request $request)
    {
        $this->store($request);
        return $this->show();
    }

    /**
     * Display the specified resource.
     */
    public function get(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }
            return JsonFormatter::successFormatJson("User retrieved successfully", 0, $user);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    public function formUpdate(Request $request, string $id)
    {
        $this->update($request, $id);
        return $this->show();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }

            $this->validate($request, User::$rules);

            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->second_lastname = $request->second_lastname;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);

            $user->update();

            return JsonFormatter::successFormatJson("User updated successfully", 0, $user);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (UniqueConstraintViolationException $e) {
            return JsonFormatter::errorFormatJson('Email already exists', -4, null);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }
            $user->delete();
            return JsonFormatter::successFormatJson("User deleted successfully", 0, null);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function formDelete($id)
    {
        $this->destroy($id);
        return $this->show();
    }

    /**
     * Cambia de rol a un usuario
     */
    public function setRole(Request $request, string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }

            $this->validate($request, [
                'role' => ['required', 'string', 'max:255']
            ]);

            $user->role = $request->role;

            $user->update();
            return JsonFormatter::successFormatJson("Role updated successfully", 0, $user);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function attachArticles(Request $request)
    {
        try {
            $failAttached = [];

            $this->validate($request, [
                'id_user' => ['required', 'numeric', 'max:255'],
                'id_articles' => ['required', 'array'],
                'positions' => ['required', 'array']
            ]);

            $user = User::find($request->id_user);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }

            $positions = $request->positions;

            foreach ($request->id_articles as $index => $article_id) {
                $articleData = Article::find($article_id);
                if (!$articleData) {
                    $failAttached[] = $article_id;
                    continue;
                }
                $user->articles()->attach($articleData->id, [
                    'position' => $positions[$index],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }

            if (count($failAttached) > 0 && count($request->id_articles) === count($failAttached)) {
                return JsonFormatter::errorFormatJson('Articles not found', -3, null);
            } else if (count($failAttached) > 0) {
                return JsonFormatter::successFormatJson('Articles attached successfully with errors', 1, [
                    "message" => "Articles ID not found",
                    "articles" => $failAttached
                ]);
            }
            return JsonFormatter::successFormatJson("Article attached successfully", 0, null);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function detachArticles(Request $request)
    {
        try {
            $this->validate($request, [
                'id_user' => ['required', 'numeric', 'max:255'],
                'id_articles' => ['required', 'array']
            ]);
            $user = User::find($request->id_user);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }
            $user->articles()->detach($request->id_articles);
            return JsonFormatter::successFormatJson("Articles detached successfully", 0, null);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function payments(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }
            $payments = $user->payments;
            return JsonFormatter::successFormatJson("Payments retrieved successfully", 0, $payments);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function articles(string $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }
            $articles = $user->articles;
            return JsonFormatter::successFormatJson("Articles retrieved successfully", 0, $articles);
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function importUsers(Request $request)
    {
        try {
            $this->validate($request, [
                'file' => 'required|file|mimes:xls,xlsx,csv',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();

            $data = [];
            foreach ($sheet->getRowIterator(2, $sheet->getHighestDataRow()) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = $rowData;
            }

            $authors = [];
            $iterator = 0;

            // Separacion de nombres y correos
            foreach ($data as $key => $row) {
                $splitNames = explode(",", str_replace(" and ", ",", ($row[1] ? $row[1] : "NULL"))); // Separacion
                $namesFixed = array_map("trim", $splitNames); // Remover espacios

                $splitMails = explode(",", ($row[5] ? $row[5] : "NULL")); // Separacion
                $mailsFixed = array_map("trim", $splitMails); // Remover espacios

                for ($i = 0; $i < count($namesFixed); $i++) {
                    if (!isset($mailsFixed[$i])) {
                        $mailsFixed[$i] = "NULL";
                    }
                }

                for ($i = 0; $i < count($splitNames); $i++) {
                    $authors[$iterator] = [$iterator, $namesFixed[$i] === "NULL" ? "NULL" : $namesFixed[$i], $mailsFixed[$i] === "NULL" ? "UndefinedMail_" . Str::uuid() : $mailsFixed[$i]];
                    $iterator++;
                }
            }

            // Limpiar autores duplicados
            foreach ($authors as $key => $author) {
                $mail = $author[2];
                foreach ($authors as $i => $row) {
                    if ($mail === $row[2] && $key != $i) {
                        unset($authors[$i]);
                    }
                }
            }

            $iterator = 0;
            // Creacion de usuarios
            foreach ($authors as $key => $row) {
                $mail = $row[2];
                $searchUser = User::where("email", "=", $mail)->get();

                if (count($searchUser) > 0) {
                    continue;
                }

                $user = new User();

                $user->role = USER::$ROLE_AUTHOR;
                $user->name = $row[1];
                $user->email = $row[2];
                $user->password = bcrypt("o.Zg4546_wfXfGHr@3PMvbC4");

                $user->save();
                $iterator++;
            }

            return JsonFormatter::successFormatJson('Users imported successfully', '0', 'created ' . $iterator . ' users');
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return JsonFormatter::errorFormatJson('User not found', -3, null);
            }

            if (!Hash::check($request->password, $user->password)) {
                return JsonFormatter::errorFormatJson('Invalid credentials', -2, null);
            }

            return JsonFormatter::successFormatJson('User logged in successfully', 0, $user);
        } catch (ValidationException $e) {
            return JsonFormatter::errorFormatJson('Validation error', -2, $e->getMessage());
        } catch (Exception $e) {
            return JsonFormatter::errorFormatJson('Something went wrong', -1, $e->getMessage());
        }
    }
}

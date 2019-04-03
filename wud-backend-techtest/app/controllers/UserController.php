<?php

class UserController extends \BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        switch (Request::get('with')) {
            case 'userScores':
            $data = DB::table('users')
            ->join('user_scores', 'users.id', '=', 'user_scores.user_id')
            ->where('user_scores.year', '=', '2016')
            ->get();
            break;
            default:
                $users = User::all();
                $data = $users->toArray();
            break;
        }

        return Response::json([
                'error' => false,
                'data' => $data
                ], 200, [], JSON_PRETTY_PRINT
        );
    }

    public function create()
    {
        return View::make('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make(
            Request::all(),
            array(
                'firstname'  => 'required|max:50',
                'lastname'  => 'required|max:50',
                'email' => 'required|email|unique:users',
            )
        );

        if ($validator->fails())
        {
            return $validator->messages();
        }

        $user = new User;
        $user->firstname = Request::get('firstname');
        $user->lastname = Request::get('lastname');
        $user->email = Request::get('email');

        $user->save();


        if (\Config::get('app.email_from') == 'phpmailer') {
            // TODO
        }
        else {
            try {
                $data = array();
                \Mail::send('emails.welcome', $data, function ($message, $user) {
                    $message->from(\Config::get('app.email_from'));
                    $message->to($user->email);
                });
            }
            catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        return Response::json([
                'error' => false,
                'data' => $user->toArray()
                ], 200
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return Response::json([
                'error' => false,
                'data' => $user->toArray()
                ], 200, [], JSON_PRETTY_PRINT
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $user = User::find($id);
        $user->firstname = Request::get('firstname');
        $user->lastname = Request::get('lastname');
        $user->email = Request::get('email');
        $user->save();

        return Response::json([
                'error' => false,
                'data' => $user->toArray()
                ], 200
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return Response::json([], 204);
    }
}

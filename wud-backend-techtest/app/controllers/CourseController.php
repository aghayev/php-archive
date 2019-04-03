<?php

//list, create, retrieve, update and delete the courses.

class CourseController extends \BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $courses = Course::all();

        return Response::json([
                'error' => false,
                'data' => $courses->toArray()
            ], 200, [], JSON_PRETTY_PRINT
        );
    }

    public function create()
    {
        return View::make('courses.create');
    }

    /**
     * Store a newly created resource in create.
     *
     * @return Response
     */
    public function store()
    {
        $validator = Validator::make(
            Request::all(),
            array(
                'name' => 'required|unique:courses',
            )
        );

        if ($validator->fails())
        {
            return $validator->messages();
        }

        $course = new Course;
        $course->name = Request::get('name');
        $course->save();

        return Response::json([
                'error' => false,
                'data' => $course->toArray()
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
        $course = Course::findOrFail($id);

        return Response::json([
                'error' => false,
                'data' => $course->toArray()
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
        $course = Course::find($id);
        $course->name = Request::get('name');
        $course->save();

        return Response::json([
                'error' => false,
                'data' => $course->toArray()
            ], 200
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function delete($id)
    {
        $course = Course::find($id);
        $course->delete();

        return Response::json([], 204);
    }
}

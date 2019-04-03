<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">

    <nav class="navbar navbar-inverse">
        <ul class="nav navbar-nav">
            <li><a href="/api/v1/courses">View All Courses</a></li>
            <li><a href="/api/v1/courses/create">Create a Course</a>
        </ul>
    </nav>

    <h1>Create a Course</h1>

    <!-- if there are creation errors, they will show here -->
    {{ HTML::ul($errors->all()) }}

    {{ Form::open(array('url' => '/api/v1/courses')) }}

    <div class="form-group">
        {{ Form::label('name', 'Name') }}
        {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    </div>

    {{ Form::submit('Create the Course!', array('class' => 'btn btn-primary')) }}

    {{ Form::close() }}

</div>
</body>
</html>
@extends('layouts.default')

@section('content')

<div class="row">

    <!-- The file upload form used as target for the file upload widget -->
    <form id="fileupload" action="{{URL::to("uploadImage")}}" method="POST" enctype="multipart/form-data">
        <input type="file" name="files[]" multiple>
        <input type="submit" value="enviar">
    </form>
</div>
@stop
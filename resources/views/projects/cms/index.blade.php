@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="btn-primary rounded f-14 mt-5" data-toggle="modal" data-target="#addcmsmodal">
                    <i class="fa fa-plus mr-1"></i>Add CMS
                </button>
                @include('projects.modals.addcmsmodal')
                <div class="card mt-3">
                    <div class="card-header bg-white border-0 text-capitalize d-flex justify-content-between p-20">
                        <h4 class="f-18 f-w-500 mb-0">CMS</h4>
                    </div>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $key = $all_cms->currentPage() * $all_cms->perPage() - $all_cms->perPage() + 1;
                        @endphp
                        @foreach($all_cms as $cms)
                            <tr>
                                <td>{{ $key++ }}</td>
                                <td class="text-center"> {{$cms->cms_name}}</td>
                                <td class="text-center">
                                    <a href="" class="btn btn-primary update_cms_form" data-toggle="modal" data-target="#editcmsmodal" data-id="{{ $cms->id }}" data-name="{{ $cms->cms_name }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="row my-3 ml-3">{{ $all_cms->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    @include('projects.modals.editcmsmodal')
@endsection

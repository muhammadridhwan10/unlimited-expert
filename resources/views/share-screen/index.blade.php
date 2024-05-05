@extends('layouts.admin')
@section('page-title')
    {{__('Share Screen')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Share Screen')}}</li>
@endsection

@push('css-page')
    <style>
        .camera-button {
            width: 50px;
            height: 50px;
            background-color: #f26622;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .camera-button:hover {
            background-color: #f26622;
        }

        .camera-button i {
            color: #fff;
        }
    </style>
@endpush


@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body mt-2">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label">{{__('Enter Room ID or create Room')}}</label>
                                <input id="room-input" type="text" class="form-control" placeholder="{{__('Room ID')}}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary mb-3" onclick="createRoom()">{{__('Create Room')}}</button>
                                <button type="submit" class="btn btn-primary mb-3" onclick="joinRoom()">{{__('Join Room')}}</button>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="container">
                            <div class="row">
                                <div class="col text-center" id="notification" hidden>
                                    <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6" id="local-vid-container" hidden>
                                    <div class="text-center">
                                        <video id="local-video" controls class="col-12 local-video"></video>
                                    </div>
                                </div>

                                <div class="col-md-6" id="remote-vid-container" hidden>
                                    <div class="text-center">
                                        <video id="remote-video" controls class="col-12 remote-video"></video>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="buttons" hidden>
                                <div class="col-md-12 text-center"> 
                                    <div class="btn-group" role="group" aria-label="Camera Controls">
                                        <div class="camera-button" onclick="toggleCamera()">
                                            <i id="cameraIcon" class="fas fa-video"></i>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" onclick="startScreenShare()">{{__('Share Screen')}}</button>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col text-center" id="screenshare-container" style="display: none;">
                                    <video height="300" id="screenshared-video" controls class="local-video"></video>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script-page')
    <script src="{{ asset('assets/js/share-screen/peerjs.min.js') }}"></script>
    <script src="{{ asset('assets/js/share-screen/function.js') }}"></script>
@endpush

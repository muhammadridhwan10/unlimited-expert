var room_id;
var getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
var local_stream;
var screenStream;
var peer = null;
var currentPeer = null
var screenSharing = false
var cameraOn = true;
var microphoneOn = true;

function toggleCamera() {
    if (cameraOn) {
        stopCamera();
    } else {
        startCamera();
    }
}

function toggleMicrophone() {
    if (microphoneOn) {
        stopMicrophone();
    } else {
        startMicrophone();
    }
}

function startMicrophone() {
    getUserMedia({ video: true, audio: true }, (stream) => {
        console.log(stream);
        local_stream = stream;
        setLocalStream(local_stream);
        microphoneOn = true;
        document.getElementById("microphoneIcon").classList.remove("fa-microphone-slash");
        document.getElementById("microphoneIcon").classList.add("fa-microphone");
        document.getElementById("microphoneStatus").innerText = "Turn Off Microphone";
    }, (err) => {
        console.log(err);
    });
}

function stopMicrophone() {
    local_stream.getAudioTracks().forEach(track => track.stop());
    microphoneOn = false;
    document.getElementById("microphoneIcon").classList.remove("fa-microphone");
    document.getElementById("microphoneIcon").classList.add("fa-microphone-slash");
    document.getElementById("microphoneStatus").innerText = "Turn On Microphone";
}

function startCamera() {
    getUserMedia({ video: true, audio: true }, (stream) => {
        console.log(stream);
        local_stream = stream;
        setLocalStream(local_stream);
        cameraOn = true;
        document.getElementById("cameraIcon").classList.remove("fa-video-slash"); 
        document.getElementById("cameraIcon").classList.add("fa-video");
        document.getElementById("cameraStatus").innerText = "Turn Off Camera";
    }, (err) => {
        console.log(err);
    });
}

function stopCamera() {
    local_stream.getVideoTracks().forEach(track => track.stop());
    document.getElementById("local-video").srcObject = null;
    cameraOn = false;
    document.getElementById("cameraIcon").classList.remove("fa-video");
    document.getElementById("cameraIcon").classList.add("fa-video-slash");
    document.getElementById("cameraStatus").innerText = "Turn On Camera";
}

function createRoom() {
    console.log("Creating Room")
    let room = document.getElementById("room-input").value;
    if (room == " " || room == "") {
        alert("Please enter room number")
        return;
    }

    room_id = room;

    //$.ajax({
    //    url: "{{ route('create-room') }}",
    //    type: "POST",
    //    data: { room_id: room_id, _token: $('meta[name="csrf-token"]').attr('content') },
    //    success: function(response) {
    //        if (response.success) {
    //            alert(response.message);
    //        } else {
    //            alert("Failed to create room!");
    //        }
    //    },
    //    error: function(xhr) {
    //        console.log(xhr.responseText);
    //        alert("Error occurred while creating room. Please try again.");
    //    }
    //});

    peer = new Peer(room_id)
    peer.on('open', (id) => {
        console.log("Peer Room ID: ", id)
        getUserMedia({ video: true, audio: true }, (stream) => {
            console.log(stream);
            local_stream = stream;
            setLocalStream(local_stream)
        }, (err) => {
            console.log(err)
        })
        //notify("Waiting for peer to join.")
        alert("Room created successfully!");
    })
    peer.on('call', (call) => {
        call.answer(local_stream);
        call.on('stream', (stream) => {
            console.log("got call");
            console.log(stream);
            setRemoteStream(stream)
        })
        currentPeer = call;
    })
}

function setLocalStream(stream) {
    document.getElementById("local-vid-container").hidden = false;
        document.getElementById("buttons").hidden = false;
    let video = document.getElementById("local-video");
    video.srcObject = stream;
    video.muted = true;
    video.play();
}
function setScreenSharingStream(stream) {
    document.getElementById("screenshare-container").hidden = false;
    let video = document.getElementById("screenshared-video");
    video.srcObject = stream;
    video.muted = true;
    video.play();
}
function setRemoteStream(stream) {
    document.getElementById("remote-vid-container").hidden = false;
    let video = document.getElementById("remote-video");
    video.srcObject = stream;
    video.play();
}


function notify(msg) {
    let notification = document.getElementById("notification")
    notification.innerHTML = msg
    notification.hidden = false
    setTimeout(() => {
        notification.hidden = true;
    }, 3000)
}

function joinRoom() {
    console.log("Joining Room")
    let room = document.getElementById("room-input").value;
    if (room == " " || room == "") {
        alert("Please enter room number")
        return;
    }

    //let csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

    //let data = {
    //    room_id: room,
    //    _token: csrfToken
    //};


    //$.ajax({
    //url: "{{ route('join-room') }}",
    //type: "POST",
    //data: data,
    //success: function(response) {
    //    if (response.success) {
    //        alert(response.message);
    //    } else {
    //        alert("The room id you entered is incorrect!");
    //    }
    //},
    //    error: function(xhr) {
    //        console.log(xhr.responseText);
    //        alert("Error occurred while joining room. Please try again.");
    //    }
    //});

    room_id = room;
    peer = new Peer()
    peer.on('open', (id) => {
        console.log("Connected room with Id: " + id)

        getUserMedia({ video: true, audio: false }, (stream) => {
            local_stream = stream;
            setLocalStream(local_stream)
            //notify("Joining peer")
            alert("Successfully joined the room!");
            let call = peer.call(room_id, stream)
            call.on('stream', (stream) => {
                setRemoteStream(stream);

            })
            currentPeer = call;
        }, (err) => {
            console.log(err)
        })

    })
}


function startScreenShare() {
    if (screenSharing) {
        stopScreenSharing()
    }
    navigator.mediaDevices.getDisplayMedia({ video: true }).then((stream) => {
        setScreenSharingStream(stream);

        screenStream = stream;
        let videoTrack = screenStream.getVideoTracks()[0];
        videoTrack.onended = () => {
            stopScreenSharing()
        }
        if (peer) {
            let sender = currentPeer.peerConnection.getSenders().find(function (s) {
                return s.track.kind == videoTrack.kind;
            })
            sender.replaceTrack(videoTrack)
            screenSharing = true
        }
        console.log(screenStream)
    })
}

function stopScreenSharing() {
    if (!screenSharing) return;
    let videoTrack = local_stream.getVideoTracks()[0];
    if (peer) {
        let sender = currentPeer.peerConnection.getSenders().find(function (s) {
            return s.track.kind == videoTrack.kind;
        })
        sender.replaceTrack(videoTrack)
    }
    screenStream.getTracks().forEach(function (track) {
        track.stop();
    });
    screenSharing = false
}
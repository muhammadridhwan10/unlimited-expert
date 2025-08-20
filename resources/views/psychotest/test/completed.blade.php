<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Completed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .completed-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="completed-card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h2 class="mb-3">Test Completed Successfully!</h2>
                        <p class="text-muted mb-4">
                            Thank you for completing the psychotest assessment. 
                            Your responses have been recorded and will be reviewed by our team.
                        </p>
                        
                        @if(session('message'))
                            <div class="alert alert-info">
                                {{ session('message') }}
                            </div>
                        @endif
                        
                        <div class="alert alert-light">
                            <h6><i class="fas fa-info-circle"></i> What's Next?</h6>
                            <p class="mb-0 small">
                                You will be contacted by our HR team regarding the next steps in the recruitment process.
                                Please keep an eye on your email for further communications.
                            </p>
                        </div>
                        
                        <p class="text-muted small">
                            You may now close this window.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
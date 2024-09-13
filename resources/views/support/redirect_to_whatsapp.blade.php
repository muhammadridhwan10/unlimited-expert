<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to WhatsApp...</title>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {

            function redirectToWhatsApp() {
                window.open("{{ $whatsappUrl }}", '_blank');
            }

            function redirectToSupport() {
                window.location.href = "{{ route('support.index') }}";
            }

            setTimeout(redirectToWhatsApp, 1000);

            setTimeout(redirectToSupport, 5000);
        });
    </script>
</head>
<body>
    <p>Redirecting to WhatsApp...</p>
</body>
</html>

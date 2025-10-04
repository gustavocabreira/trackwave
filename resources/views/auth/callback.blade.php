<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Autenticando...</title>
</head>
<body>
  <script>
    const token = '{{ $token ?? null }}';
    const error = '{{ $error ?? null }}';

    const data = {
      token: token,
      error: error
    };

    if (window.opener) {
      window.opener.postMessage(data, '*');
      window.close();
    }
  </script>
</body>
</html>
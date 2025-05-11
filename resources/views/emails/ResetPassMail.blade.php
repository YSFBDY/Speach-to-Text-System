<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Increased width */
            width: 100%;
            margin: auto;
        }
        .navbar {
            background:rgb(13, 173, 157);
            color: white;
            padding: 15px;
            font-size: 20px; /* Slightly larger */
            font-weight: bold;
            border-radius: 8px 8px 0 0;
            max-width: 600px; /* Increased width */
            width: 100%;
            margin: auto;
        }
        h2 {
            color: #333;
            font-size: 22px;
        }
        .otp {
            font-size: 28px; /* Slightly larger */
            font-weight: bold;
            color: rgb(13, 173, 157);
            padding: 12px;
            border: 2px dashed rgb(13, 173, 157);
            display: inline-block;
            margin: 10px auto;
            text-align: center;
            

        }
        p {
            color: #666;
            font-size: 18px; /* Slightly larger */
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="navbar">Speach To Text</div>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>You requested to reset your password. Use the verification code below:</p>
        <div class="otp">{{ $otp }}</div>
        <p>The verification code will be valid for <b>30 minutes</b>. Please do not share this code with anyone.</p>
        
        <p class="footer">
            This is an automated message, please do not reply.
        </p>
        <p class="footer">
            Thanks,<br>
            <strong>Speach To Text Team</strong>
        </p>
    </div>
</body>
</html>

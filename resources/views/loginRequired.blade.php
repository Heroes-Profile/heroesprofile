<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Required | Heroes Profile</title>
    <link rel="icon" type="image/png" href="/images/logo/heroesprofilelogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Ruslan+Display&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #0F121D;
            color: #ffffff;
            font-size: 18px;
            letter-spacing: .14em;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Nav bar */
        .nav {
            background-color: #333;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav a {
            display: flex;
            align-items: center;
            font-family: 'Ruslan Display', cursive;
            font-size: 1.5rem;
            color: #ffffff;
            text-decoration: none;
        }
        .nav img {
            width: 2.5rem;
            margin: 0 0.5rem;
        }

        /* Main content */
        .main {
            max-width: 1500px;
            margin: 0 auto;
            padding: 2rem 1rem;
            flex: 1;
        }
        h1 {
            font-size: 1.8rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1rem;
        }
        .subtitle {
            text-align: center;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .description {
            color: #aaa;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        /* Two-column layout */
        .columns {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .column {
            flex: 1;
            line-height: 1.7;
        }
        .divider-v {
            width: 2px;
            background: rgba(255,255,255,0.1);
        }

        /* Button */
        .btn {
            display: inline-block;
            padding: 0.85rem 2rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            color: #ffffff;
            margin-top: 1.5rem;
            text-align: center;
        }
        .btn-blue { background: #213d7a; }
        .btn-blue:hover { background: #315bb6; }
        .btn-red { background: #b33616; }
        .btn-red:hover { background: #e44a23; }

        /* Info box */
        .info-box {
            max-width: 900px;
            margin: 0 auto;
            color: #aaa;
            line-height: 1.7;
        }

        /* Footer */
        .footer {
            margin-top: auto;
            background: rgba(255,255,255,0.1);
            border-top: 4px solid #008b8b;
            text-align: center;
            padding: 2rem 1rem;
        }
        .footer a {
            color: #ffffff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Ruslan Display', cursive;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .footer img {
            width: 2.5rem;
            margin: 0 0.5rem;
        }
        .footer .copyright {
            font-size: 0.85rem;
            color: #aaa;
        }
        .footer .copyright a {
            display: inline;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.85rem;
            color: #aaa;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .columns { flex-direction: column; }
            .divider-v { width: 100%; height: 2px; }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="/">
            Heroes
            <img src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo">
            Profile
        </a>
    </div>

    <div class="main">
        <h1>Login Required</h1>
        <p class="description">
            Due to heavy bot and scraping traffic, we are temporarily requiring all users
            to log in with their Battle.net account before accessing the site.
            Feel free to contact us at zemill@heroesprofile.com
        </p>
        <hr style="border-color: rgba(255,255,255,0.1); margin-bottom: 1.5rem;">
        <p class="subtitle">To access the site, please log in with your Battle.net account:</p>

        <div class="columns">
            <div class="column">
                If you already have a Battle.net account, click the button below to log in.
                Once authenticated, you will have full access to all site features.
                <br>
                <a href="/Authenticate/Battlenet" class="btn btn-blue">Log in with Battle.net</a>
            </div>
            <div class="divider-v"></div>
            <div class="column">
                Please consider supporting the site by donating on Patreon.
                Your support helps us cover server costs and keep the site running for the community.
                <br>
                <a href="https://www.patreon.com/c/heroesprofile" class="btn btn-red" target="_blank">Donate on Patreon</a>
            </div>
        </div>

        <div class="info-box">
            <strong style="color: #ffffff;">Why is this happening?</strong><br>
            We are currently experiencing an unusually high volume of automated traffic
            that is impacting site performance for everyone. Requiring authentication
            helps us protect the site and ensure a smooth experience for real users.
            This is a temporary measure and will be removed once the situation is resolved.
        </div>
    </div>

    <div class="footer">
        <a href="/">
            Heroes
            <img src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo">
            Profile
        </a>
        <div class="copyright">Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a></div>
    </div>
</body>
</html>

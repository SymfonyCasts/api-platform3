# Tokens Cookies

Coming soon...

Oh, API authentication. We have sessions, we have API tokens, we have OAuth, we have
JSON web tokens. What do we need for our situation? This can get complex. First I
want you to ask, who will be using my api? Is it A your own JavaScript or B, do you
need to allow programmatic access? Like some code somewhere will be talking to your
api. We're gonna go through both of these use cases and each of them have some extra
complexities that we'll discuss along the way. By the way, when you think of API
authentication, you typically think of API tokens, and that's true, but it turns out
that pretty much all authentication is done via some sort of a token. Even session
based authentication is done by sending a cookie, which contains a unique, you
guessed it token. The trick is figuring out which type of token you need to send in
each situation and how the end user will get that token. So let's talk about that
first use case. The user of your API is your own JavaScript. So first, before we even
talk about security, make sure your front end and your A and your API live on the
same domain, like the exact same domain. Why? Because if they live on two different
domains or subdomains, you have to deal with cores,

Cross origin resource, something that adds complexity to your application and also
hurts performance. Kevin Douglas, the lead developer on APL platform, has a blog post
about this where he even shows you how you can have two separate code repositories
for your backend and your front end if you want that. And still, I have them served
under the same domain using some web server magic. If you do for some reason decide
to put things on different sub-domains, then you can, you'll need to worry about
Coors headers and you can solve that with Neel cores bundle, but I don't recommend it
anyways. If you are using your API from your own JavaScript, you're probably log, the
user is probably logging into a login form with an email and password. It doesn't
matter if that's a traditional login form or one that's built with some fancy
JavaScript framework that submits VA J. So again, when you think about API
authentication, you think about tokens like I, I need a P I tokens, right? And the
answer is not necessarily a really simple restful way to handle authentication from
your own job script would be just to use H G T P basic authentication on all of your
endpoints.

Like the user enters an email and password and then you make an AX request to some
endpoint just to make sure if that's valid,

Then you store that username and password in JavaScript and you send it on every
single API request going forward. However, this might be a bit problematic cuz
there's the question of where do you securely store the email and password in
JavaScript so you can continually use it. This is actually a problem in general with
JavaScript. Even if you have an API token, you need to be very careful where you
store it so that other JavaScript on your page can't read it. There are solutions,
but it's a bit of complexity that you likely don't need. So instead for your own
JavaScript, you, you can use a session. When you start a session in Symphony, it
returns a cookie that is H T T P only, and that cookie contains a token and actually
the contents of the HTTP only cookie aren't really important. It could be a session
id, some sort of a token, whatever. But session cookies are sessions. Cookies are
automatic and easy. The really important thing is that because the cookie is HTTP
only, it can't be read by JavaScript, your JavaScript or anyone else's JavaScript,
but whenever you make an A A P I request that cookie's gonna come with it and it's
gonna authenticate you a safe, secure way to store your token, which in this case is
your session identifier.

So the API token in this situation is simply the session cookie storaged securely in
an HTT V only cookie. Mm. We will go through this use case. Oh, and by the way, one
edge case with this situation is if you have a single sign on situation, in that
case, you'll authenticate with your S sso like a normal web app.

You'll end up with an access token, which you can, and then you can choose to just
log the user in, uh, via session authentication like normal or use that access token
directly from your JavaScript. That's a more advanced use case that we won't go
through. In this tutorial though, we will talk about how to read API tokens in your
API regardless of where those come from. Now the second big use case is programmatic
access. Some code will use your api. In this case, the API clients absolutely will be
sending some sort of an API token string. And so you need to make your API able to
read a token that's sent on each request and it's usually sent on an authorization
header. How the user gets this token depends, there are kind of two main cases. The
first case is the GitHub personal access token situation. This is where you can,
where a user on your site can actually browse your site and choose to create a new
access token that they can go copy themselves and then go use inside of their code.
The second big situation is O OWA is just a way to get an access token and it's
especially important if the API client,

The person making the API request, is not directly the use. The the, the code that's
making the API request is making those API requests on behalf of some user on your
system. So imagine a website that allows you to connect with Facebook or any other
system so that the website can then make API requests to Facebook for your account or
an iPhone app where to log in. You show the use of the login of your site and
ultimately VN O off flow. That mobile app gets an access token it can use to talk to
your API on behalf of that user. We're gonna talk about the per personal access token
method in this tutorial, and we'll talk about how to read and validate API tokens no
matter where they come from. We won't talk about the oof flow and it's partially
because it's kind of a separate thing. Yes, if you have this use case where you need
to allow third parties to get access tokens for different users on your site, you
will need some sort of OAuth server, whether you build it yourself or use some other
solution. But once the OAuth server has done its work,

The client will talk to your API with a good old fashioned API token. So your API
will need to read, validate, and, and understand that token, but it doesn't care how
the user got it. Next, let's go through the first use case, allowing our JavaScript
to log in by sending an AX request.


# API Login Form with json_login

On the homepage, which is built in Vue, we have a login form. The goal is that,
when we submit this, it will send an AJAX request with the email & password to and
endpoint that will validate it.

The form itself is built over here in `assets/vue/LoginForm.vue`. If you're not
familiar with Vue, don't worry. We *will* do some light coding in it, but I'm mostly
using it as a small example to make some API requests.

Down near the bottom, on submit, we make a POST request to `/login` sending the
`email` and `password` as JSON. So our *first* goal is to create *this* endpoint.

## Creating the Login Controller

Fortunately, Symfony has a built-in mechanism *just* for this. To start, even though
it won't do much, we need a new controller! In `src/Controller/`, create a new PHP
class. Let's call it `SecurityController`. This will look very: extend `AbstractController`,
then add a `public function login()` that will return a `Response`, the one from
HttpFoundation.

Above, give this a `Route` with a URL of `/login` to match what our JavaScript is
sending to. And, name the route: `app_login`. Oh, and we don't really need to do
this, but we can also add `methods: ['POST']`. There won't be a `/login` *page*
on our site: we're only going to POST to this URL.

## Returning the Current User Id

As you'll see in a minute, we're not going to *process* the `email` and `password`
in this controller... but this controller *will* be executed after a successful login.
So... what *should* we return after a successful login? I don't know! And honestly
it mostly depends on what would be useful in our JavaScript. I haven't thought about
it much yet, but maybe... the user id? Let's start there.

If authentication *was* successful, then, at this point, the user will be logged
in like normal. So, to get the currently-authenticated user, I'm going to leverage
a newer feature of Symfony Add an argument with a PHP attribute called `#[CurrentUser]`.
Then we can use the normal `User` type-hint, call it `$user` and default it to `null`,
in case we're not logged in for some reason. We'll talk about how that's possible
in a minute.

Then, return `$this->json()` and with a `user` key set to `$user->getId()`.

Cool! And that's *all* we need our controller to do.

## Activating json_login

To activate the system that will do the *real* work of reading the email & password,
head to `/config/packages/security.yaml`. Under the firewall, add `json_login` and
below that `check_path`... which will be set to the name of the *route* that we just
created. So, `app_login`.

This activates a security listener: it's a bit of code that will now be watching
*every* request to see if it is a POST request to this route. So, a POST to `/login`.
If it *is*, it will decode the JSON on the request, read the `email` and `password`
*off* of it, and validate the password and log us in.

Though, we *do* need to tell it what *keys* in the JSON we're using. As a reminder,
we're using the keys `email` and `password`: super creative. So below this, set
`username_path` to `email` and `password_path` to `password`.

## The User Provider

Done! But wait! If we POST an `email` and `password` to this endpoint... how the
heck does the system know how to *find* that user? How is it supposed to know that
it should query the `user` table `WHERE email = ` the email in the request?

Excellent question! In episode 1, we ran the

```terminal
php bin/console make:user
```

command. This created a `User` entity with the basic security stuff that we need.
In `security.yaml`, it *also* created a user provider. This is an entity provider:
it basically tells the security system to find users in the database by querying
by the `email` property. This means our system will decode the JSON, fetch the
`email` key, query for a `User` with a matching email, then validate the password.
In other words... we're ready!

Looking back at `LoginForm.vue`, the JavaScript is *also* ready: `handleSubmit()`
will be called and we submit the form... it makes the AJAX call.

So let's try this thing! Move over and refresh just to be sure. Try it with a fake
email and password first. Submit and... nothing happened? Ppen up your browser's
inspector and go to the console. Yes! You can see a 401 status code and it dumped
this error: invalid credentials. That's coming from right here in our JavaScript:
after the request finishes, if the response is "not okay" - meaning there was an
error - we're decoding the JSON and logging it.

Apparently, when we fail authentication with `json_login`, it returns a small
bit of JSON with "Invalid Credentials".

Next: let's turn this error into something we can *see* on the form, handle *another*
error case, and then think about what to do when authentication is successful.

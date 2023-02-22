# Handling Authentication Errors

When we log in with an invalid email and password, it looks like the `json_login`
system is sending back some nice JSON with an `error.value` key set to "Invalid
credentials". If we wanted to customize this, we could create a class that implements
`AuthenticationFailureHandlerInterface` and then set its service ID onto the
`failure_handler` option under `json_login`.

## Showing the Error on the Form

*But*, this is plenty good for us. So let's use it over in our
`/assets/vue/LoginForm.vue`. We won't go too deeply into Vue, but I already have
state called `error`... and if we *set* that, it will show up on the form.

After making the request, if the response is *not* okay, We're already decoding
the JSON. Now let's say `error.value = data.error.value`.

To see I this works, make sure you have Webpack Encore running in the background
so it recompiles over JavaScript. Refresh. And... you can click this little
link to cheat and enter a valid email. But then type in a ridiculous password and...
perfect! We see "Invalid credentials" on top with some red boxes!

## json_login Requires Content-Type: application/json

So the AJAX call is working great. Though, there is *one* gotcha with the `json_login`
security mechanism: it requires you to send a `Content-Type` header set to
`application/json`. We *are* doing setting this on our Ajax call and ou should
to. But... if someone forgets, we want to make sure that things don't explode.

Comment out that `Content-Type` header so we can see what happens. Then move over,
refresh the page... type a ridiculous password and... it clears the form? Look
down at the Network here. The endpoint returned a 200 status code with a `user`
key set to `null`!

And... that makes sense! Because we're missing the header, the `json_login` mechanism
did *nothing*. Instead, the request continued to our `SecurityController`... except
that *this* time the user is *not* logged in. So, we return `user: null`... with
a 200 status code.

That's a problem because make it *look* like the Ajax call was successful. To fix
this, if, for *any* reason the `json_login` mechanism was skipped but the user is
hitting our login endpoint, let's return a 401 status code that says:

> Hey! You need to log in!

So, if *not* `$user`, then `return $this->json()`... and this could look like anything.
I'll include an `error` key explaining what probably went wrong. That matches the
`error` key that `json_login` returns when the credentials fail. Heck. I'll even
fix my typo!

*Most* importantly, for the second argument, pass a 401 for the status code.

Below, we can simplify... because now we know that there *will* be a user.

Beautiful! Spin over and submit another bad password. Oh, gorgeous! The 401 status
code triggers our error handling code, which displays the error on top. So awesome.

Go back to `LoginForm.vue` put the `Content-Type` header back.

Next: let's login *successfully* and... figure out what we want to do when that
happens! We're also going to talk about this session and how those authenticate our
API requests.

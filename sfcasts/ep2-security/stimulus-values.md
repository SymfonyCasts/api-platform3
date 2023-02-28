# Passing Values to Stimulus

Setting a global variable is find. But if you're using Stimulus, we can, instead,
pass the user data as a *value* to a Stimulus controller.

So this is a Vue app, but if you look in `templates/main/homepage.html.twig`,
we're using the `symfony/ux-vue` package to render this. Behind the scenes, this
activates a small Stimulus controller that starts & renders the Vue component. Any
arguments that we pass here are passed to the Stimulus controller as a value...
and then forwarded as props to the Vue app. So what we're going to do is "kind
of" specific to Vue, but you could use this strategy to pass any values to any
Stimulus controller.

First in the Vue component, let's allow a new prop to be passed in called `user`.
If you're not using Vue, don't worry too much about the specifics. To make sure
that's getting here `console.log(props.user)`. And initialize the data to
`props.user`.

Next, over in `base.html.twig`, remove all that fancy `window.user` stuff. And
in `homepage.html.twig`, pass a new `user` prop set to `app.user`.

Now if you move over and refresh, that's doesn't work? It looks like we're
authenticated as... nothing?

## Serializing Before Passing in the Value

If you dig a little, you'll see that we're sending the `user` to Stimulus as
empty `{}`. Why? Because when you send data into Stimulus, it doesn't use the
serializer to transform into JSON: it just uses `json_encode()`. And that's not
good enough.

So, we need to serialize it ourselves. To do that, open
`src/Controller/MainController.php`. Here's the controller that renders that template.
Autowire a service called `NormalizerInterface` and then pass a variable into our
template called `userData` set to `$normalizer->normalize()`. Oh, but we need the
user! Add another argument to the controller with the fancy new
`#[CurrentUser]` attribute, type-hint `User`, say `$user`, and then = `null` in
case we're not authenticated. Back down below, normalization will turn the object
into an array. So pass `$user` and then the format for the array, which is `jsonld`:
we want all the JSON-LD fields. Then pass the serialization context with
`'groups' => 'user:read'`.

Last step! In the template, set that `user` prop set to `userData`.

Since the Stimulus system *will* run that array through `json_encode()` that will
transform that array into JSON. When we move over and refresh.... got it! You can
see the entire JSON being passed into the Stimulus controller... and then that's
passed to Vue as a prop. 

Spin back over and make sure to get that `console.log()` out of there.

## CSRF Protection

We haven't actually seen it yet, but when we start making requests to our API, those
requests *will* be authenticated thanks to the session. When using sessions on your
API, you might read about needing CSRF protection. Do we need CSRF tokens?

The quick answer is: probably not. As long as you use something called SameSite
cookies - which we are automatic in Symfony - then your API probably doesn't need
to worry about CSRF protection. But be aware of two things. First, make sure that
your GET requests don't have any side effects. Don't do something silly like allow
the API client to make a GET request... but then you save something to the database.
Second, some older browsers - like IE 11 - don't support SameSite cookies. So by
forgoing CSRF tokens, you could be allowing a small percentage of your users to
be susceptible to CSRF attacks.

If you want to learn more, our API Platform 2 tutorial has a whole chapter on
SameSite cookies and CSRF tokens.

Next, logging in from our own JavaScript with session authentication was delightfully
simple! So now let's turn to the other use-case: API tokens.

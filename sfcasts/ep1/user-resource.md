# User API Resource

We have a `User` entity... but it is not *yet* part of our API. How *do* we make
it part of the API? Ah, we already know! Go above the class and add the `ApiResource`
attribute.

[[[ code('92949c6bd5') ]]]

Refresh the docs. Look at that! Six fresh new endpoints for the `User` class! And
thanks to our fixtures, we *should* be able to see data immediately. Let's try the
collection endpoint. Execute and... it's alive.

Though... it *is* a little weird that fields like `roles` and `password` show up.
Ah, we'll worry about that in a minute.

## API Platform & UUIDs

Before we keep rolling forward, I want to mention one quick thing about UUIDs. As
you can see, we're using auto-increment IDs for of our API - it's always
`/api/users/` then the entity id. But you can *totally* use a `UUID` instead. And
that's something we'll do in a future tutorial.

But... why *would* you use UUIDs? Well, sometimes it can make life easier in
JavaScript when working with frontend frameworks. You can actually *generate* the
`UUID` in JavaScript and then send that to your API when creating a new resource.
This can help because your JavaScript knows the ID of the resource immediately
and can update the state... instead of waiting for the Ajax request to finish
to get the new auto-increment id.

Anyways, my point is: API Platform *does* support `UUIDs`. You could add a new
UUID column, then tell API Platform that it should be your *identifier*. Oh, but
keep in mind that some database engines - like MySQL - can have poor performance
if you make the UUID the primary key. In that case, just keep `id` as the primary
key, and add an *extra* UUID column.

## Adding the Serialization Groups

Anyways, back to our `User` resource! Right now, it's returning *way* too many fields.
Fortunately, we know how to fix that. Up on `ApiResource`, add a
`normalizationContext` key with `groups` set to `user:read` to follow the same
pattern that we used in `DragonTreasure`. Also add `denormalizationContext`
set to `user:write`.

[[[ code('815bd94371') ]]]

Now we can just decorate the fields that we want in the API. We don't
need `id`... since we always have `@id`, which is more useful. But we
*do* want `email`. So add the `#Groups()` attribute, hit tab to add
that `use` statement and pass both `user:read` and `user:write`.

[[[ code('6d252dc798') ]]]

Copy that... and go down to `password`. We *do* need the password to be writeable
but not readable. So add `user:write`.

[[[ code('8e24ec96a7') ]]]

Now this still isn't quite correct. The `password` field should hold the *hashed*
password. But our users will, of course, send the plaintext passwords via the API
when creating a user or updating their password. Then *we* will hash it. That's
something we're going to solve in a future tutorial when we talk more about security.
But this will be good enough for now.

Oh, and above `username`, also add `user:read` and `user:write`.

[[[ code('51e0656fd4') ]]]

Cool! Refresh the docs... and open up the collections endpoint to give it a go.
The result... exactly what we wanted! Only `email` and `username` come back.

And if we were to *create* a new user... yup! The writable fields are `email`,
`username`, and `password`.

## Adding Validation

Ok, what else are we missing? How about validation? If we try the
POST endpoint with empty data... we get that nasty 500 error. Fixing time!

Back over in the file, start *above* the class to make sure that both `email` and
`username` are `unique`. Add `UniqueEntity` passing `fields` set to `email`...
and we can even include a message. Repeat that *same* thing... but change `email`
to `username`.

[[[ code('80dc514174') ]]]

Next, down in `email`, add `NotBlank`... then I'll add the `Assert` in front...
and tweak the `use` statement so it works just like last time.

[[[ code('be7c823ff0') ]]]

Nice. email needs one more - `Assert\Email` - and above `username`, add `NotBlank`.

[[[ code('0d0ce6f949') ]]]

I'm not too worried about `password` right now... because it's already a bit weird.

Let's try this! Scroll up and *just* send a `password` field. And... yes! The
nice 422 status code with validation errors. Try valid data now: pass an `email` and `username`... though I'm not sure this guy's actually a dragon... we might need
a captcha.

Hit Execute. That's it! 201 status code with `email` and `username` returned!

Our resource has validation, pagination and contains great *information*!
And we could even easily add filtering. In other words, we're crushing it!

And *now* we get to the *really* interesting part. We need to "relate" our two
resources so that each treasure is *owned* by a user. What does that look like in
API Platform? It's super interesting, and it's next.

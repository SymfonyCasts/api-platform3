# Serialization Groups: Choosing Fields

Right now, whether or not a field in our class is readable or writable in the API
is *entirely* determined by whether or not that property is readable or writable in
our class (basically, whether or not it has a getter or setter method). But what
if you *need* a getter or setter... but *don't* want that field exposed in the API?
For that, we have two options.

## A DTO Class?

Option número uno: create a DTO class for the API resource. This is something
we'll save for another day... in a future tutorial. But in a nutshell, it's where
you create a dedicated class for your `DragonTreasure` API... and then move the
`ApiResource` attribute onto *that*. The key thing is that you'll design the new
class to look *exactly* like your API... because modeling your API will be its *only*
job. It takes a little more work to set things up, but the advantage is that you
then have a dedicated class for your API. Done!

## Hello Serialization Groups

The *second* solution, and the one we're going to use, is *serialization groups*.
Check it out. Over on the `ApiResource` attribute, add a new option called
`normalizationContext`. If you recall, "normalization" is the process of going
from an object to an array, like when you're making a `GET` request to read a
treasure. The `normalizationContext` is basically *options* that are passed to
the serializer during that process. And the *one* option that's most important
is `groups`. Set that to one group called `treasure:read`:

[[[ code('7093028e4d') ]]]

We'll talk about what this does in a minute. But you can see the pattern I'm using
for the group: the name of the class (it could be `dragon_treasure` if we wanted)
then `:read`... because normalization means that we're *reading* this class. You
can name these groups however you want: this is my standard.

So... what does that *do*? Let's find out! Refresh the documentation... and, to make
life easier, go to the URL: `/api/dragon_treasures.jsonld`. Whoops! It's just
`treasures.jsonld` now. There we go. And... absolutely nothing is returned! Ok,
we have the hydra fields, but this `hydra:member` contains the array of treasures.
It *is* returning one treasure... but other than `@id` and `@type`... there are
no actual fields!

## How Serialization Groups Work

Here's the deal. As soon as we add a `normalizationContext` with a group,
when our object is normalized, the serializer will *only* include properties
that have this group on it. And since we haven't added *any* groups to our properties,
it returns *nothing*.

How do we add groups? With *another* attribute! Above the `$name` property, say
`#[Groups]`, hit "tab" to add its `use` statement and then `treasure:read`. Repeat
this above the `$description` field... because we want *that* to be readable...
and then the `$value` field... and finally `$coolFactor`:

[[[ code('d97dbaf7a2') ]]]

Good start. Move over and refresh the endpoint. Now... got it! We see `name`,
`description`, `value`, and `coolFactor`.

## DenormalizationContext: Controlling Writable Groups

We now have control over which fields are *readable*... and we can do the same thing
to choose which fields should be *writeable* in the API. That's called
"de-normalization", and I bet you can guess what we're going to do. Copy
`normalizationContext`, paste, change it to `denormalizationContext`... and use
`treasure:write`:

[[[ code('6cd21e5fb1') ]]]

Now head down to the `$name` property and add `treasure:write`. I'm going to skip
`$description` (remember that we actually *deleted* our `setDescription()`
method earlier on purpose)... but add this to `$value`... and `$coolFactor`:

[[[ code('50c5182dc1') ]]]

Oh, it's *mad* at me! As soon as we pass *multiple* groups, we need to make this
an *array*. Add some `[]` around those three properties. Much happier.

To check if this is A-OK, refresh the documentation... open up the `PUT` endpoint,
and... sweet! We see `name`, `value`, and `coolFactor`, which are currently the *only*
fields that are *writable* in our API.

## Adding Groups To Methods

We *are* missing a few things, though. Earlier, we made a `getPlunderedAtAgo()`
method... 

[[[ code('d5521f7bac') ]]]

and we want this to be included when we *read* our resource. Right now,
if we we check the endpoint, it's *not* there.

To fix this, we can *also* add groups above methods. Say
`#[Groups(['treasure:read'])]`:

[[[ code('71791d8a66') ]]]

And when we go check... *voilà*, it pops up.

Let's also find the `setTextDescription()` method... and do the same thing:
`#[Groups([treasure:write])]`:

[[[ code('18514dde07') ]]]

Awesome! If we head back to the documentation, the field is not currently there...
but when we refresh... and check out the `PUT` endpoint again... `textDescription`
is *back*!

## Re-Adding Methods

Hey, now we can re-add any of the getter or setter methods we removed earlier!
Like, maybe I *do* need a `setDescription()` method in my code for something. Copy
`setName()` to be lazy, paste and change "name" to "description" in a few places.

Got it! And even though we have that setter back, when we look at the `PUT`
endpoint, `description` *doesn't* show up. We have complete control over our fields
thanks to the denormalization groups. Do the same thing for `setPlunderedAt()`...
because sometimes it's handy - in data fixtures especially - to be able to *set*
this manually.

And... done!

## Adding Field Defaults

So we know that *fetching* a resource works. Now let's see if we can *create* a new
resource. Click on the `POST` endpoint, hit "Try it out", and... let's fill in
some info about our new treasure, which is, of course, a `Giant jar of pickles`.
This is *very* valuable and has a `coolFactor` of `10`. I'll also add a
description... though this jar of pickles speaks for itself.

When we try this... oh, dear... we get a 500 error:

> An exception occurred while executing a query: Not null violation, `null`
> value in column `isPublished`.

We slimmed our API down to *only* the fields that we want *writeable*... but
there's still one property that *must* be set in the database. Scroll up and find
`isPublished`. Yup, it currently defaults to `null`. Change that to `= false`...
and now the property will *never* be `null`.

If we try it... the `Giant jar of pickles` is pickled into the database!
It works!

Next: let's explore a few more cool serialization tricks to give us even
more control.

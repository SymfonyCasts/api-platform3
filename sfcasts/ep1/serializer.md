# The Serializer

The key behind how API platform turns our objects into JSON... and also how it
transforms JSON back into objects is Symfony's Serializer. `symfony/serializer`
is a standalone component that you can use outside of API platform and it's
*awesome*. You give it any input - like an object or something else - and it
transform that into any format, like `JSON`, `XML` or `CSV`.

## The Internals of the Serializer

As you can see in this fancy diagram, it goes through two steps. First, it takes
your data and *normalizes* it into an array. Second, it *encodes* that into the
final format. It can also do the same thing in reverse. If we're starting with
JSON, like we're sending JSON to our API, it first *decodes* it to an array and then
*denormalizes* it back into an object.

For all of this to happen, internally, there are many different normalizer
objects that know how to work with different data. For example, there's a
`DateTimeNormalizer` that's really great at handling `DateTime` objects. Check it
out: our entity has a `createdAt` field, which is a `DateTime` object:

[[[ code('d716c7b2e1') ]]]

If you look at our API, when we try the `GET` endpoint, this is returned as a 
special date time *string*. The `DateTimeNormalizer` is responsible for doing that.

## Figuring out Which Fields to Serialize

There's also another really important normalizer called the `ObjectNormalizer`. Its
job is to read properties off of an object so that *those* properties can be
normalized. To do that, it uses another component called `property-access`.
*That* component is smart.

For example, looking at our API, when we make a GET request to the collection
endpoint, one of the fields it returns is `name`. But if we look at the class,
`name` is a *private* property:

[[[ code('7109160cd3') ]]]

So how the heck is it reading that?

*That's* where the `PropertyAccess` component comes in. It first looks to see if the
`name` property is public. And if it's not, it then looks for a `getName()` method:

[[[ code('dbfdaf13f2') ]]]

So *that* is what's actually called when building the JSON.

The same thing happens when we *send* JSON, like to create or update a `DragonTreasure`.
PropertyAccess looks at each field in the JSON and, if that field is settable, like
via a `setName()` method, it sets it:

[[[ code('c34e8dc7af') ]]]

And, it's even a bit cooler than that: it will even look for getter or setter methods 
that don't correspond to *any* real property! You can use this to create "extra" 
fields in your API that don't exist as properties in your class.

## Adding a Virtual "textDescription" Field

Let's try that! Pretend that, when we're creating or editing a treasure, instead
of sending a `description` field, we want to be able to send a `textDescription`
field that contains plaintext... but with line breaks. Then, in our code, we'll
transform those lines breaks into HTML `<br>` tags.

Let me show you what I mean. Copy the `setDescription()` method. Then, below,
paste and call this new method `setTextDescription()`. It's basically going to set
the `description` property... but call `nl2br()` on it first. That function
literally transforms new lines into `<br>` tags. If you've been around as long as
I have, you remember when `nl2br` was super cool:

[[[ code('6c03aea9ba') ]]]

Anyways, with *just* that change, refresh the documentation and open the POST or PUT
endpoints. Woh! We have a new field called `textDescription`! Yup! The serializer
*saw* the `setTextDescription()` method and determined that `textDescription` is
a "settable" virtual property!

However, we *don't* see this on the GET endpoint. And that's perfect! There is
no `getTextDescription()` method, so there will *not* be a new field here. The
new field is *writable*, but not readable.

Let's take this endpoint for a spin! First... I need to execute the GET
collection endpoint so I can see what ids we have in the database. Perfect: I
have a Treasure with ID 1. Close this up. Let's try the PUT endpoint to do our
first update. When you use the PUT endpoint, you *don't* need send every field:
only the fields you want to change.

***TIP
If you're starting a new API Platform project, this `PUT` request will fail!
You can try a `PATCH` request instead. This relates to a new `standard_put`
config in `config/packages/api_platform.yaml`, which we talk about a bit
later in the tutorial.
***

Pass `textDescription`... and I'll include `\n` to represent some new lines in
JSON.

When we try it, yes! 200 status code. And check it out: the `description` field
has those `<br>` tags!

## Removing Fields

Ok, so now that we have `setTextDescription()`... maybe that's the *only* way that
we want to allow that field to be set. To enforce that, eradicate the `setDescription()`
method.

Now when we refresh... and look at the PUT endpoint, we still have `textDescription`,
but the `description` field is gone! The serializer realizes that it's no longer
settable and removed it from our API. It would still be *returned* because it's
something that we can read, but it's no longer writeable.

This is all *really* awesome. *We* simply worry about writing our class the way we
want then API Platform builds our API accordingly.

## Making the plunderedAt Field Readonly

Ok, what else? Well, it *is* a little weird that we can set the `createdAt` field:
that's usually set internally and automatically. Let's fix that.

Oh, but, ya know what? I meant to call this field `plunderedAt`. I'll refactor
and rename that property... then let PhpStorm also rename my getter and setter
methods.

Cool! This will *also* cause the column in my database to change... so spin
over to your console and run:

```terminal
symfony console make:migration
```

I'll live dangerously and run that immediately:

```terminal
symfony console doctrine:migrations:migrate
```

Done! Thanks to that rename... over in the API, excellent: the field is now
`plunderedAt`.

Ok, so forget about the API for a moment: let's just do a little cleanup. The purpose
of this `plunderedAt` field is for it to be set automatically whenever we create
a new `DragonTreasure`.

To do that, create a `public function __construct()` and, inside, say
`this->plunderedAt = new DateTimeImmutable()`. And now we don't need the `= null`
on the property.

[[[ code('aa4f1718e5') ]]]

And if we search for `setPlunderedAt`, we don't really need that method anymore!
Remove it!

This now means that the `plunderedAt` property is readable but not writeable. So,
no shocker, when we refresh and open up the `PUT` or `POST` endpoint, `plunderedAt`
is absent. But if we look at what the model would look like if we *fetched* a
treasure, `plunderedAt` is still there.

## Adding a Fake "Date Ago" Field

All right, one more goal! Let's add a virtual field called `plunderedAtAgo` that
returns a human-readable version of the date, like "two months ago". To do
this, we need to install a new package:

```terminal
composer require nesbot/carbon
```

Once this finishes... find the `getPlunderedAt()` method, copy it, paste below,
it will return a `string` and call it `getPlunderedAtAgo()`. Inside, return
`Carbon::instance($this->getPlunderedAt()))` then `->diffForHumans()`.

[[[ code('4eeb57fbcf') ]]]

So, as we now understand, there is *no* `plunderedAtAgo` property... but the
`serializer` *should* see this as readable via its getter and expose it.
Oh, and while I'm here, I'll add a little documentation above to
describe the field's meaning.

Ok, let's try this. As soon as we refresh and open a `GET` endpoint, we see the
new field under the example! We can also see the fields we'll receive down in the
Schemas section. Back up, let's try the `GET` endpoint with ID `one`. And...
how cool is that?

Next: what if we *do* want to have certain getter or setter methods in our class,
like `setDescription()`, but we do *not* want that to be part of our API? The
answer: serialization groups.

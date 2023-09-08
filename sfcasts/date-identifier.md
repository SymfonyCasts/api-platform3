# Using a Custom (Date) Identifier

For our `DailyQuest` API endpoints, we set up an `id` as the identifier. But
what we *really* want is a date... so we can have fancy URLs like
`/api/quests/2023-06-05`.

Let's try it! In `DailyQuest`, instead of `public int $id`, say
`public \DateTimeInterface $day`. And in the constructor, replace the argument with
`\DateTimeInterface $day`... and `$this->day = $day`.

Next, in `DailyQuestStateProvider`, we'll say... how about `new \DateTime('now')`
and `new \DateTime('yesterday')`.

When we refresh the docs... we're back to where we were before: we're missing
the ID on `PUT`, `DELETE`, and `PATCH`, and our single `GET` is *gone*. That's because
API Platform doesn't know that the `$day` property is meant to be our identifier.
Though, if we try the `GET` collection endpoint... hey! The `day` field *does* show
up inside the JSON like a normal property!

What we want to do is tell API Platform:

> Hey! This isn't a normal property: `day` is our identifier.

We do that by adding an `#[ApiProperty]` attribute above this with `identifier: true`.

## Debugging IRI Generation Errors

When we check, this *does*, in fact, fix all of our routes. But when we try the
collection endpoint... we get a 400 error:

> Unable to generate an IRI for the item of type `DailyQuest`.

So API Platform *loaded* our two `DailyQuest` objects... but when it tried to generate
the `@id` property (the IRI), for some reason, it exploded!

To find out more, go down to the web debug toolbar and open up that request in the
profiler. On the Exception tab, there were *two* exceptions on this page: a
*nested* exception situation.

The top level - `Unable to generate an IRI` - doesn't really tell us *why* there
was a problem. Down here, we can see:

> We were not able to resolve the identifier matching parameter "day".

This error isn't *super* clear either, but it's closer. It's *really* saying:

> Yo! I tried to generate the IRI by using the `day` field... but that's a
> `DateTimeInterface` object... and I don't know how to convert that to a string.

We actually chose a pretty tricky IRI to work with, and I think that's cool.
API Platform *does* have a system called "URI variable transformer". The `{day}`
is a *variable* in the route... and you can help "transform" the `DateTimeInterface`
object into something that can be used in that string. The "Identifiers" documentation
talks about this.

But there's also a simple solution. Create a new function called `getDayString()`
which will return a `string`. Inside, `return $this->day->format()` with the format
we want: `Y-m-d`.

## Making a Method the Identifier

The trick is to make this *method* the identifier: move the `ApiProperty`
from the actual property... down above this.

Perfect! Back over here... the routes still look correct. You can see we have
`{dayString}` now. And when we try our `GET` collection endpoint... check it out!
We see `"@id": "/api/quests/` and then the date string. That's *exactly* what we
wanted!

*Though*, now we have a `dayString` field in the JSON... as well as the `day` itself.
Let's think. We really *don't* need the `day` field at all: it exists internally
just to help the `dayString`. And because the `dayString` is in the URL, having
that as a field *also* seems unnecessary. Can we hide these?

## Hiding Specific Fields from your API

Sure! And we don't even need to use serialization groups! We're going to go deeper
into this later, but above the `day` property, we can hide this *entirely* from
our API by using an `#[Ignore]` attribute from Symfony's serializer.

If we head over here and "Execute" that... boom! That field is gone: it can't
be read or written.

We *could* do the same thing for `getDayString()`. But *another* option is to say
`readable: false`. This means it won't be *readable*, but it will still *technically*
be writable. However, because there's no `setDayString`, it's not actually writable.

Now, when we "Execute" this... that field disappears too.

*This* is the setup we want! We have the ID we want, we don't have any extra fields
that we *don't* want, and we can now *add* whatever fields that we *do* want. To
do *that*, we're going to build an Enum.

Create a `src/Enum/` directory... and, inside, a new PHP class, or really enum, called
`DailyQuestStatusEnum`. I'll paste some code here.

This is just a way for us to keep track of the *status* of each `DailyQuest`. Back
over in that class, let's add some properties: `public string $questName`,
`public string $description`.... and whatever other properties we need in our API,
like `public int $difficultyLevel`, and a `public DailyQuestStatusEnum` called
`$status`.

## Null Fields are Hidden

Nice! Let's try this! Head over... and Execute! Hmm, we don't see any of the new
fields yet. That's because they're not *populated* and, by default, API Platform
*hides* fields that are null or uninitialized.

But if we refresh the page and go down to the documentation for the response... it
shows that these *are* part of the API.

Head over to `DailyQuestStateProvider` so we can populate them. Say
`return $this->createQuests()`: a new private function we'll create. I'll paste
that in as well: you can grab the code from the code block on this page.

This creates *50* quests - each one a day further in the past - and populates
simple data for the rest of the fields. Some of the quests will be `ACTIVE`, and
others `COMPLETED`.

Oh, and notice that I'm using `getDayString()` as the key for this array. We don't
*need* to do that: they keys in the array returned by your collection provider
are *not* important. I only did this because it's going to be handy in a few minutes
when we create the get one operation.

Testing time! Move over, hit "Execute" again and... look at that! We have 50 items
with data on *all* of them. That's *gorgeous*!

Next: Let's get our provider working for the item operations: meaning when we fetch
a *single* item. The item provider is used for the GET one operation, `PUT`,
`PATCH` and `DELETE`.

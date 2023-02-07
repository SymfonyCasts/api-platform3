# PropertyFilter: Sparse Fieldsets

Since dragons *love* expensive treasure, let's add a way for them to filter based
on the *value*, like within a range. There's a built-in filter for that called
`RangeFilter`. Find the `$value` property and, like we did before, use
`#[ApiFilter()]` and inside `RangeFilter` (the one from ORM) `::class`:

[[[ code('94173682b5') ]]]

This one doesn't need any other options, so... we're done! Dang... that was easy.
When we refresh... open it up, and hit "Try it out".... look at that! We have
*a ton* of new filters - `value[between]`, `value[gt]` (or "greater than"),
`value[gte]`, etc. Let's try `value[gt]`... with a random number... maybe `500000`.
When we click "Execute"... yup! It updated the URL here. It's... not the prettiest
URL ever - due to the encoding - but it *works* like a charm. And down in the results...
apparently there are 18 treasures worth more than that!

## PropertyFilter

The *last* filter I want to show you... isn't really a filter at all. It's a way
our API clients to choose which *fields* they want returned... instead of which
*results*.

To show this off, find the `getDescription()` method. Pretend that we want to return
a shorter, truncated version of the description. To do this, copy the
`getDescription()` method, paste it below, and rename it to `getShortDescription()`:

[[[ code('b23723799d') ]]]

To *truncate* this, we can use the `u()` function from Symfony. Type `u` and make
sure to hit "tab" to autocomplete that. This is a rare *function* that Symfony
gives us... and hitting "tab" *did* add a `use` statement for it:

[[[ code('b36939414f') ]]]

This creates an object with all sorts of string-related goodies on it, including
`truncate()`. Pass 40 to truncate at `40` characters followed by `...`.

Method done! To expose this to our API, above, add the `Groups` attribute with
`treasure:read`:

[[[ code('dd0b92e22a') ]]]

Beautiful! Okay, head back to the documentation and refresh. Open the `GET` endpoint,
hit "Try it out", "Execute" and... *nice*. Here's our truncated description!

Though... it *is* weird that we now return *two* descriptions: a short one and
the regular one. If our API client wants the short description, it may not want
us to *also* return the full-length description... for the sake of bandwidth.

What can we do? Introducing: the `PropertyFilter`! Head back to `DragonTreasure`.
Unlike the others, this filter *must* go above the class. So right here, say
`ApiFilter`, and then `PropertyFilter` (in this case, there's only *one* of them)
`::class`. There *are* some options you can pass to this - which you can find in
the docs - but we don't need any of them:

[[[ code('29aa3955b8') ]]]

So... what does that *do*? Head back, refresh the documentation, open up the
GET collection endpoint, and hit "Try it out". Woh! We now see a `properties[]`
box and we can add items to it. Let's try it! Add a new string called `name`
and another called `description`.

Moment of truth. Hit "Execute", and... there it is! It popped these onto the URL
like normal. But look at the response: it *only* contains the `name` and `description`
fields. Well... it *also* contains the JSON-LD fields, but the *real* data is *just*
those two fields.

If we removed the `properties` strings, we would get the normal, full response.
So, by default, you get *all* fields. But users can now *choose* fewer fields
if they want to.

## What about Vulcain?

This all *works* quite nicely. But if you look at the API Platform documentation
for the `PropertyFilter`, they actually recommend a different solution: something
called "Vulcain". Nope, not Spock's home planet. We're talking about a protocol
that adds features to your web server. It was created by the API Platform team, and
if we scroll down a bit, they have a really good example.

Pretend that we have the following API. If we make a request to `/books`, we
get these two books back. Simple enough. Then maybe we want to get more info
about the *first* book, so we make a request to *that* URL - `/books/1`. Great!
But... now we want details about the author, so we make a request to
`/authors/1`.

So, to get *all* the book information and all the author information, we ultimately
needed to make *four* requests: the original, plus 3 more. That's not great for
performance.

What Vulcain allows you to do is just make this *first* request... but tell the server
that it should *push* the data from the other requests *to* you.

We can see this best in JavaScript, and there's a little example down here. In
this case, imagine that we're making a request to `/books/1` but we know that
we also need the author information. So, when we make the request, we include
a special `Preload` header. This tells the server:

> Hey! After returning the book data, use a server push to send me the information
> found by following the `author` IRI.

The *really* cool thing is that your JavaScript doesn't really change. You *still*
use `fetch()` to make a second request to the `bookJSON.author` URL... except
that this will return *instantly* because the browser already has the data.

I'm not going to get into all the specifics, but the `Preload` on the first example
is even more impressive: `/member/*/author`. That tells the server to push all
the data as if we had *also* requested each of the `member` keys - so all the books -
*and* their author URLs.

The point is: if you use Vulcain, your API users can make *tiny* changes to enjoy
huge performance benefits... without us needing to add a lot of fanciness to our
API.

Next: Let's talk about *formats*. We know that our API can return JSON-LD, JSON,
and even HTML representations of our resources. Let's add two *new* formats, including
a CSV format, which will be the *fastest* CSV export feature you've ever built.

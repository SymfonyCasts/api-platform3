# Browser

Coming soon...

Let's make this test real with some data and assertions. So there are two main ways
to do assertions with Browser. First, it comes with a bunch of built-in methods to
help you do assertions, like `assertJson()`. Or you can always just grab the JSON
that comes back from one of these endpoints and do the assertions with normal PHP
unit assertions. It's super flexible and I'll show you how to do both. So first, we
can just assert that, is this JSON or not? We try that, it passes. So we're also
going to want to assert a couple other things. Like we know that our API responses
should come back with a `hydra:totalItems` property set to the number of items. Right
now we don't have any items, but we can at least assert that that matches zero. So we
can say `assertJsonMatches()`. This is something specific to Browser and it uses a
special syntax where you can actually read different keys. I'm going to talk more
about that in a second. This one's pretty simple, right? It's just grabbing a key and
asserting that that = zero. Now when you try this, it's actually going to fail with
an interesting error. It says logic exception empty-dowling JMSPath.php is required
to search JSON. So we actually need to install that. So I'm going to copy that
Composer require line `composer require mtdowling/jmespath.php --dev` and we'll
install that. This JMSPath thing is actually a bit of a tool. There's this kind of
standard way, standard expression you can use to get different keys in JSON. So for
example, if this is your JSON and you want to read the key A, that just = foo.
Simple. But you can also do deeper things like A.B.C.D and that will give you value.
And it really gets kind of crazy here. You can get the first key, you can get A.B.C.D
and then the zero key and then keep going from there. You can even slice the array in
different ways. So you can actually get pretty nuts with this. And this is a really
helpful page for trying out the different combinations. We're not going to get too
crazy with it. And this is a really simple example. Now that we have the library
installed, let's run the test again. And they fail. With a really weird error, it
says syntax error at character five, `hydra:totalItems`. So unfortunately, the:is a
special syntax inside of this JMSPath thing. So whenever you have a:here, we actually
need to quote this. Looks a little funny, we're actually going to put double quotes
around that little part here. Now when we do that, beautiful, it passes. Cool. But
this is not a very interesting test. Just testing that there's zero results because
our database is empty. Really to make our test nice, we need data. We need to seed
our database with data at the beginning of the test. Fortunately, Foundry makes that
super simple. At the top of our test, call `DragonTreasureFactory::createMany(5);`
And let's create five dragon treasures. And down here, we'll assert that we get five
back. It's just that simple. And actually, let me put our dump back here just so we
can keep seeing the dump results. Now when we try it, it passes still and check this
out if you look up. Yeah, that's coming back. We have five treasures in there.
Foundry makes adding that data so simple. All right, so we might also want to test
that we get the exact fields back that we get all these fields back and not more or
less. So how can we do that with that special JMS J M E S syntax? Well, this
`assertJsonMatches()` thing is really handy. And actually behind the scenes, if I
kind of hold command or control and click into this whenever you call when you call
`assertJsonMatches()`, what this is actually doing is behind the scenes, it's calling
this error JSON. This creates a JSON object and.json object actually has additional
methods to help you do things with JSON. So out of the box of browser, we have
`assertJsonMatches()`. But if we want access to.json object and those other methods,
we can do that. There's two ways to do that. The first is to use this handy use
function inside of browser where we pass it a call back and then this is going to
receive a JSON. Object is a little bit magical. This use function in browsers a
little bit magical. There's actually various things you can type in here. It actually
reads what you're typing in your argument and passes you what you want. So because
we're typing this JSON, it's going to grab.json object for the last response and pass
it to us. Now inside, let's do a little bit of kind of experimenting because what
we're ultimately after is we want to be able to basically be able to check what the
keys are for the first item inside of `hydra:member`. So to help kind of figure this
out, we can use a function here called search. Search is just a way to kind of use a
selector and then get back a result. So let's do double quotes and we'll say
`hydra:member`. We'll see what that gives us back. And I'm going to remove my dump
from up here. All right, let's try the test again. They pass but awesome. More
importantly, look at that. It actually got at give that gave us access to that array.
Sweet. So now we want to do is we're going to grab the zero index and then I really
want to just grab the keys here. So to do that, after our `hydra:member` double
quotes, we can say left square bracket zero right square bracket. So that will now
this now represents the first item inside of Hydra member. And then we can set a
surround that entire thing with a keys function. Try that now. And beautiful. This is
probably one of the more complex things that you can do. And I'll show you an easier
way to do this if you don't want to use the kind of special JMS syntax. All right,
let's turn this finally into an assertion. So I'll say `JSON->assertMatches()`. So
there's two things we can do here. We can just set this to a result and then do a
normal PHP assertion on there. Or you can actually replace assert with
`assertMatches()`. That's going to execute this thing here. And then for the second
argument, we can pass in what we expect it to match. So I'm going to quickly type in
all the keys. I'm getting these from right here. We know what keys we're supposed to
have. So I'll very quickly type in all the keys that we expect in our response. So
we're asserting that this expression is going to grab a result that's equal to this.
And when we try it, that passes. Awesome. All right, so this is really cool. We have
this `assertJsonMatches()`. We actually could have just, we didn't even really need
to break out into this function now that we know this. We could have moved all this
just into a normal `assertJsonMatches()`. But I also want to show you kind of a less
fancy way of doing this. So instead of the use function, what you could actually do
is just say `JSON =`. And you could break out of browser by saying `->json()`. So
most of the browser functions return an instance of browser. So we can keep chaining
things here. Some functions return something different, like JSON returns.json
object. What's nice about that is we can remove the use function here. And we can
kind of get into like more normal PHP unit coding where we just use.json object and
call `assertMatches()` on it. It's over here. Oh, that errors because I have a syntax
error. Of course, I typed. Over here, that passes. And if this is still too fancy for
you, which is totally fine, you can actually just change this to a normal PHP unit
assertion, you can say `$this->assertSame()` And instead of this fancy expression
here, we'd say `JSON->decoded()` that gives us actually decoded JSON array. And then
we can get `hydra:member`, we can grab the zero key. And then we can surround this
entire thing with `array_keys()`. So just the PHP version of the expression that we
had before. And this time, still passes. So use whatever you feel most comfortable
with. A lot of flexibility to write this test how you want. Next, let's write some
tests for authentication, both logging in via our login form and via an API token.

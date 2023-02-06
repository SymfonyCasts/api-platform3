# Subresources

Coming soon...

To get all of the Dragon treasures for a specific user, we have two different ways.
One, you could just fetch the user and read its Dragon Treasures property. The second
way is via the filter that we just added a second ago where you can actually use the
Treasures collection endpoint with a filter on it. So in the API, that just looks
like question mark, owner equals slash api slash user slash four, and then you get
the collection endpoint. This is my favorite way of doing it because I'm, if I wanna
fetch treasures, I should use the Treasures endpoint. But you can also use API
platform to get some fancier ways to do this kind of almost vanity URLs. Like imagine
to get this same collection, we wanted to be the user to be able to go to slash API
instead of this url. Well, with Jsun LD on it, we wanted the user to be able to go to
something like slash api slash users slash four slash treasures. That of course
doesn't actually work. You can see it, but that would would've been pretty cool. Well
that is called a sub resource. And sub resource sub resources are much nicer in API
platform three. So in, if you think about this situation, what this endpoint will
return is a treasure. So to add this sub resource, we're going to need to update the
Dragon Treasure class.

So I'll spin over and what we're actually gonna do is add a second API resource. So
we have our first a main API resource down here. Add another API resource and for the
first time we're gonna control the URL with A U R I template. So I'm gonna put
exactly what I want here slash users slash And then for the wild card part, I'm gonna
put user underscore id. You'll see how that's used in the moment. And then slash
treasures. And that's it. Well, I'm also gonna add dot underscore curler base
underscore format. This is optional, but that's actually what allows us to cheat and
add this dot jsun ld. So if you want that to work on your new endpoint, make sure you
include that. And in this case, for operations, we don't need all six operations.
This is gonna be, this really represents a single operation. So we'll set operations
to New Git collection because we're going to be returning a collection of treasures
for this particular user and done. So I'm gonna go back to the documentation and
refresh. And suddenly we have three resources and this one's got the correct URL
bill. We really only need re, the reason we have three resources is because if you
remember originally we customized our short name. So let's actually copy that from
here and make them match

Or to make API platform happy, please, just Aren happy. I'll put them in order now in
refresh. Perfect. That's what we want. So we see we have a new operation basically
for, for fetching our treasures. So does it already work? Well, it says it'll
retrieve a collection of treasure resources. That's good. But then there's some
problems. It thinks that we need to pass the ID of a dragon treasure here, but it
should be a user. And even if we pass something there like four, check this out and
hit execute. Look at the url it literally didn't even use that for, and it has the
Curly Brace user ID in the url. So of course it came back with a 4 0 4. The missing
piece is that we need to define what this user underscore ID is. We understand that
it's the ID of the user, so we need to describe that. Here's how add a new option
called u i variables. This is where you describe any wild cards that you have in your
url. So we're gonna pass the user underscore id, and this is gonna be set to a new
link object. And there are multiple here, but you want the one from API platform
slash metadata.

And there's basically two things you need to put in this user ID. First, you're gonna
point it at the class that this is related to. You're gonna use a from class option
set to user con con class. The second thing you're gonna do is then say, which
property on user points to Dragon Treasure? So you're gonna point it at the property
that forms the relationship and to specify that you're gonna say from property and
then you're gonna say treasures. So the idea here is that this user ID is meant to
find a user. And then these treasures, the relationship between the user and the
treasures is defined on this treasure's property. And actually that's not right, is
it? It's defined on a Dragon Treasure's property. Found my own mistake when I was
looking at it. So that's a little confusing. Don't worry too much about it. It was
actually hard for me to understand. Wrap my head around two. All right, so we go over
and refresh. Now we, let's see, see our endpoint here. And now look, it says user
identifier, so it knows that this is meant to be a user. So let's put four in there

And execute and got it. Look at that. There are our, let's see, five items, five
treasures for that user. Look over here and refresh are the url. It works. So behind
the scenes to do this, thanks to our link, it basically makes the following query,
select Star from Dragon Treasure where owner ID equals whatever we pass for user
underscore id. It figures out how to make that query by actually looking at the
doctrine relationship and figuring out which columns are used in that query. So it's
super smart. We can actually see this in the profiler. So I'm gonna go to slash
underscore profiler and then here's click on our request right there. And we can go
down and actually look at that doctrine query. So these are both basically the same
query. This is the one that's used for Page Nation. So if you do view formatted
query, you can see it. It's actually even more complex than I was talking about. It
has an inner join here, but it's basically selecting all the Dragon data where owner
ID equals the ID of that user. By the way, if you look at the documentation, there's
also a way to do this with two property owner,

Which that actually works just fine. Also don't do that, there's no problem with it.
Just use from property. Um, consistently it's more clear. Two, property is needed if
you don't, if you didn't map the inverse side of the relationship, like there was no
Dragon Treasure's property on user. So in that case you would need to use the two
property. But unless you have that situation always use from property, it's just
simpler. Don't worry about it. Now there is one small tiny problem here. If you look
back at our data, it's got the wrong fields. Look, it's returning everything like the
ID and the is published. That's not normally returned because of our normalization
groups. The reason they're being returned is that remember we specify the
normalization, normalization groups on the API resource. So they're missing from down
here. And so there are no groups. So it serializes everything it can. So it means
that we need to bring down our normalization context and add it down here. We don't
need DN normalization context because we don't have any operations that use de
normalizing. So we just need that. Lemme refresh now. Got it. All right. So let's add
one more endpoint

And actually I'll show it you URL first. So let's see. We have a treasure with ID 11.
So let's say that we wanna have something that looks like this slash api slash
treasures slash 1111. Well if we just go to that right now, we of course will get the
information about that. But what if we wanna be able to add slash owner to that to
just get that user? Of course, right now it doesn't work. But we can add that with a
sub resource. So because the resource that's gonna be returned is a user, that's
where we need to add this new API resource. So above that class, I'll keep it near
the other resource. Let's add API resource. And then we need I template set two slash
treasures slash we'll use treasure underscore ID as the wild card though this can be
anything. And then slash owner dot. And we'll do our little underscore format trick.
Okay, step two is we now need to pass in the URI variables. We have just this one
treasure underscore ID set to a new link, the one from AAP platform metadata. And we
use a normal from class. So this is referring to a Treasure Dragon treasure. So we'll
say Dragon treasure colon calling class. And then the property on Dragon Treasure
that refers to this relationship is owner.

It's all about from property owner and I'm. And finally, we know that we're also
gonna need the normalization context down here. Oh, and one of the most important
things is that we only want one operation. So I'll add operations. And this is going
to be returning a single user. So this is just new Git that should do it. Now we move
over to the documentation and refresh under user. Yep, we have a new resource and
yep, it even sees that this is the Dragon Treasure identifier. If we go over and
refresh a page over here, it works. Oh, love the handling of sub resources in APAP
platform three.


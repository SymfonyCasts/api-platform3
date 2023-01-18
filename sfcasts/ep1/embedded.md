# Embedded

Coming soon...

So when two resources are related in our api, they show up and we set them as Iris.
But you might say, Hey, could you instead actually include the data right here for
Dragon Treasure instead of the iri? So I don't need to make a second, third, fourth,
and fifth request to get that data. Yep, that's totally possible. Again, you can also
do something really cool with Vulcan, but let's learn how to embed data.

In two minutes. We have meeting with Daniel.

Let's learn how to embed data. Okay, so when we're reading, when we're, when user
objects being serialized, it uses the normalization context and it's using the user
colon read group. So that's why the email field shows up and the username field shows
up as well as the dragon treasures to make dragon treasures. To turn this dragon
treasures into an into embedded data, we need to go into Dragon Treasure and add this
same user colon read group to whatever fields we want to show up there. So check this
out. Let's go above name and add user read here, and then let's also go down and add
it to value. So as soon as we have even one property inside a dragon treasure that
uses that same normalization group, watch what happens when we execute that. Awesome.
It actually includes name and value here, and of course it's in the normal at ID and
at type. So what this means is that when you have a relation field, it can either be
a string or an embedded object. It just depends on your normalization config, which
is going to use. All right, so let's do the same thing in the other direction. So we
have a treasure whose ID is two. So I'm gonna go up here to the get single treasure
endpoint. We'll put ID two in there.

Perfect. And we see owner as an I R I string. So let's see if we can get that to be
embedded. So we know Dragon Treasure uses the treasure coin read normalization group.
So if we go into user and add that to username

<affirmative>,

That should turn it into an IRI into an embedded object, and it does owner turns into
an object and it has that username. Awesome. Well, let's also fetch a collection of
resources of treasures real quick. Let's flesh all of them. And no surprise you can
see that every single one now has an embedded owner. So another thing we can think
about is, hmm, having the owner information when I fetch a single dragon treasure is
cool, but maybe it's like, feels like overkill to have it inside the collection
endpoint. So the question is, could we embed the owner when we're fetching a single
treasure, but then use an I or I string when we're fetching a collection? The answer
is yes, but of course the more things you add like this to your api, the kind of
trickier your API gets. So check this out. This is a two-step process. First in
Dragon Treasure,

Find the GI endpoint. This is the Git single endpoint. And one of the things you can
do is actually override your normalization context here. So I'm gonna add
normalization context, and we're gonna use the normal treasure colon read that's
that's used. We're gonna also add another one called treasure item. Get a group
that's unique to this, to this operation. You can use any string that you want here.
I like to use a convention of of the name, and then this is either item or
collection. And then this is the HTTP method that's being used like Git. So that just
helps me kind of organize. So this now means that when this endpoint is used, it's
going to include all fields in both of these groups. So we can leverage that. I'll
copy this new group name here and over in our user class on the username field.
Instead of Treasure Call and Reed, we'll use that new group, which is only used on
that one endpoint. So now if we try the collection endpoint again, yes, we're back to
owner just being an I I. And if we try the get one endpoint, oh, the owner is an IRS
string here too. It shouldn't be. I'm go back, I'm gonna go back and check my work.
You probably saw my mistake. Normalization context needs to be set to groups, and
then those are the array of groups right there. So that was basically setting two
non-existent options in the normalization context. Let's try that again this time.
Got it.

So when you do things like this, it gets a little harder to keep track of what
serialization groups are being used. You can use the Profiler to help with that. So
this is our most recent request for the single treasure. If we open that up and go
down to Serializer, you can see here that it is using, you can see the data that's
being serialized, but most importantly you can show the context. So you can see
there's a group's context here set to those two groups, and it's kind of cool too.
You can see a couple of other, um, context options that are passed internally. All
right, next, let's get crazy with our relationships by allowing the username of a U
by allowing the username of a user to be updated from the Dragon Treasure endpoint.
I'll explain all of that next.


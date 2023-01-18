# Embedded Write

Coming soon...

We know that we can read the owner of a particular dragon treasure. So let me try
this collection endpoint here. And let's see, I have one of my treasures is ID two I
want to fetch and just look at what that one looks like. Perfect. So right now, cuz
of the changes we just made, owner is embedded. But when we read a ch a treasure, we
see the owner. The question is could we change the owner of a treasure? The answer
is, of course, as long as that's a writeable field, there's no problem at all. So
right now the owner of this field is user ID one. So I'll use the put end point
update user ID equals two and let's change the user two, the owner to how about API
slash user slash three? I think that's a user in my database. And again, the only
thing to know is that we change things via the I R I. So let me execute that. Oh,
syntax error. Cause I have an extra com once I actually send valid json. There we go.
And you can see the owner comes back has slash api slash user slash three. So
beautiful.

But I'm gonna do something crazier just just to see if we can do it. So this
particular treasure is now owned by user three. Let's go look at user three. So I'll
open that. Other end points put in three. Awesome. You can see the username right now
is burnout 400. Here's what I'm gonna be able to do. I wanna be able to, while I'm
updating a dragon treasure, so I can put request to this endpoint. Instead of
changing the owner, I wanna see if I've actually changed the existing owner's
username. Like something like this. Instead of sending this to an I I I'm gonna set
it to an object with an updated username property. Would that work? Let's find out.
It executes and it doesn't. It says nested documents for attribute owner are not
allowed. Use IRIS instead. So at first glance, it looks like this isn't allowed. It
looks like you can only use an IRI string here, but actually this is allowed. The
problem is that the username field is not writeable. So let's think about this
because we're updating a dragon treasure, we're updating a dragon treasure. And so
this means that it's using the DN normalization group of treasure Colon Wright.

Thanks for that. And the Treasure Wright group is above the owner property, which is
why we can change the owner. But if we wanna be able to change the owner's username,
then we also need to go into user and add that group here. So this works exactly like
embedded fields when you read them. This will now make this field in writeable when
we're using the treasure endpoints. All right, let's try it now. And it's still
doesn't work. This one's a 500 error. A new entity was found through the relationship
dragon treasure owner, but was not configured to persist. What this means is it was
ACT when AIP platform, when the serializer sees this, it's actually creating a new
user object and then setting the username and then doctrine is failing because we
never told it to persist this user object. But that's not the point. The point is we
don't want a new user object. I want to grab the existing owner and then update its
username. By the way, to make this whole thing a little bit more realistic, let's
actually add a name if you know here too, so you can see it. I'm updating my
treasure, but while I'm here I also wanna update the owner's using it. The key to
making the serializer

Use our existing owner instead of creating a new one is to add Add an at ID field set
to the I R I of our user. So slash api slash users slash three. So when the
serializer sees an ob, an object here, if it doesn't have an id, it creates a new
object. If it does have an id, it's gonna go grab this object first and then set the
fields onto it. So now when we try it, of course I have a syntax error again because
I used a single quote up here. Silly Ryan, this is why I should never write Jason by
hand. Perfect this time it works. 200 status code, although we can't really see if it
updated the username here cause we can just see what the owner is. So let's actually
close up this endpoint and go back down here, fetch user ID three and check that out.
It did change that username. So it's not super useful in this exact example, but if
you can understand this, it really makes you powerful when you're building your api.
But looking back at that put request you made, what if we did want a new object? What
if that's actually the behavior we wanted? I wanted to be able to create a brand new
user right? Now. Is that possible? The answer is yes. There's two things you would
need to do. First, you need to add a cascade, persist

On the treasure dot owner property. This is actually something we're gonna see later
in the tutorial in a different situation. The second thing you would need to do is
make sure that you've exposed all of the required fields. Right now we've only
exposed the username to be written this way. So we wouldn't, for example, be able to
pass the password or the email so we'd end up with a user object that was missing
some fields. So we would need to add this group to a couple of other fields as well.
But other than that, yes, totally possible. All right, let's try this update one more
time with the ad id.

But this time I'm gonna set the username to be empty. And remember, our username
field has a not blank on it. So this should fail validation. And yet when we try it,
we get a 200 status code. And if we go try our user endpoint down here, yeah, the
username is now empty. That's a problem. Why did that happen? This has to do with how
symphony's validation system works. So the top level entity, that object that we're
modifying here is dragon treasure. So what the validation system looks on Dragon
treasure and it executes all of the validation constraints on that. However, when it
gets to an object like our owner property, it stops. It doesn't continue to validate
that object as well. If you want that to happen, need to add a a val uh, a constraint
on this called a assert slash valid. Now on our put endpoint, if we try this again,
yep, 4 22 owner dot username, this value should not be blank. So this what we just
learned in this chapter is really cool. Being able to update an embedded object is a
really cool thing. But the cost of this is we're making our API kind of more and more
complex.

So you can choose to this, but you might also just force your users to update the
treasurer first and then update the user in a second request instead of allowing them
to do it all fancy at the same time. All right, next, let's look at this relationship
from the other side. When we're updating a user, could we also update the treasures
that belong to that user? Let's dive into modifying the collection side of a
relationship next.


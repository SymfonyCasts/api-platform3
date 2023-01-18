# Collections Write

Coming soon...

Let's fetch a single user in our api. I know I have one I with ID three and cool. So
as we learned earlier, exposing a collection relation properties, just like any other
field, just make sure that it's in the correct serialization group. And then you can
go further with serialization groups to change it to be from an array of i I strings
or an array of embedded objects like we have now. But my question now is, could we
also modify the dragon treasures from the user endpoints? The answer is of course,
yes. And we're going to do this in increasingly crazy ways. So if we look at the post
endpoint, you don't see dragon treasures in there right now because the field simply
isn't writeable, it's not in the right group. So to make it writeable, we know what
to do. We'll just add user on. Right? And now back over on our documentation. No
surprise. There we go. Dragon treasures. And what's in expecting is an array of
strings, an array of I R I strings. So let's try this. Let's create a new user, fill
in the email and fill in the username. And then I'm gonna assign this two existing
treasures that are in our system. So I'm actually gonna cheat up here real quick and
use the get collection endpoint for treasures and Perfect. So I have ID two and ID
three.

Cool. So let's take down here. So slash api slash treasures.

Oops

Slash two and slash api slash treasures slash three. Ah heck, let's get greedy API
slash treasure slash four. So just like when we read it, it can be I an I I, there's
no problems at all with us setting it so when you execute, that worked perfectly. So
we kind of stole those treasures from someone else. But wait a second, how did that
work? We know that when we send fields like email, password, and username, because
those are private properties that you use as the setter. So when we pass username, it
calls set username. But when we pass, so who pass dragon treasures, it must call it
set dragon treasures.

But guess what? We don't have a set dragon treasures, but we do have an ad dragon
treasure method and a removed dragon treasure method. So the serializer is really
smart. It sees that our user object has no dragon treasures. And so it recognizes
that each of these three objects are new dragon treasures. And so it actually called
add dragon treasure one time for each of those dragon treasures. And really
importantly, the way that make SD generates these methods is it makes sure that it
takes that dragon treasure and sets the owner to be this object. The reason that's
important has to do with doctrine, relationships and the owning versus the inverse
side. But the takeaway is that thanks to add dragon treasure being called, and the
way this method is written, it gets our data, our data set up exactly like it needs
to. And then when everything saves, it saves correctly. So sweet. Next, let's get
more complex. By allowing treasures to be created when we're creating a new user,
we're also going to remove treasures from a user. Like in the event that the dwarves
take back the mountain as if.


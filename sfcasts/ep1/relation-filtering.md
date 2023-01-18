# Relation Filtering

Coming soon...

Earlier we added a bunch of nice filters to our Dragon Treasure resource. Well, let's
add some to user to show off some of the filtering superpowers of relations. So the
first, we're gonna start with, first thing I want we wanna talk about is add the add
AP filter like always, and then add property filter. Now this's not really a filter,
it lets you select which fields you want returned in the response. So there's nothing
very new here. When we refresh and go to the get collection endpoint, there is a new
properties thing and we could, for example, say username or we could return to
username and dragon treasures. Wyoming mean execute perfect. There they are. Username
and dragon treasures. And because our dragon treasures are embedded, we see the
embedded objects, but this is where it's even cooler. Could we have it return
username and just the name of the Dragon Treasure? Absolutely. To do that? No, I'm
not exactly sure if the syntax supports it up here. What I'm gonna do is copy our URL
over here. Let's add a little dot jsun n LD at the end. Perfect. And check this out.
The syntax is a little complicated, but you can actually say it. Let screw bracket
dragon treasures. Whoops.

And then another set of screw brackets and then equals name. And just like that,
you've got just the name returned. So just out of the box, that property filter does
allow you to kind of reach across the relationships. All right, let's do something
else. Back in Dragon Treasure. Let's say that sometimes we wanna be able to filter by
the owner. That's actually actually really handy. Show me all dragon treasures for a
specific owner. So to do that above the owner property, we'll add our normal API
filter. And this is going to be the normal search filter. And then we'll have, and
this guys do the strategy exact, so we'll say exactly which user we want. All right,
now back over on the docks, we open up the get to collection endpoint for treasures
and hit try it out. Let's see, here we go, owner. So we can say something like slash
api slash users slash four, assuming that's actually a real user we have in our
database. And perfect. There we go. There are the five treasures owned by that user.
But I wanna go further. I wanna be able to, could I actually find all treasures that
are owned by a user with a certain username? So instead of filtering on user, on
owner, we kind of wanna filter on owner dot username.

So check this out. When we wanna filter an owner, we can put this a API filter right
above the owner property. But now I wanna filter on owner username and that's not, we
don't have a property, so we can't put that in above a property. So instead, this is
one of those cases where we need to put a filter above the class. And then I
configure this since we're not above a property, we'll add a properties options set
to an array. And here we'll say owner username, and then set that to the strategy,
which will use the partial strategy. All right, so let's refresh and try that. Uh,
first I need to actually figure out what actually, I know we have smog. So let's go
back to our get collection endpoint. Owner dot username. I'll put in MOG so we can
see that at matches. Just the middle hit. Okay. Execute and that works. Pretty cool.
All right, next it's our last topic. Sub resources. This is something that has
totally changed in API platform three.


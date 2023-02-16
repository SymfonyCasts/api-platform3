# Acl Owner

Coming soon...

Okay, new security goal. I want to allow only the owner of a treasure to edit it.
Right now you're allowed to edit a treasure as long as you have this role, which
means you can edit anyone's treasure. Not cool. So let's write a test for this. At
the bottom I'll say public function test patch two, update treasure. And we'll kind
of start our normal way here. We'll say user equals user factory. Create one and then
browser this arrow browser. And then we'll say acting as user. I'm gonna use this for
authentication for most of our tests. Now it's very easy and they'll use the patch
method or slash api slash treasures slash And then actually we're gonna need a
treasure here to edit. So let's also create one up here. Treasure equals tr dragon
treasure factory, create one. And for this test, we wanna make sure that the owner is
definitely this user. So we're gonna test that. If we go and try to edit that
treasure, a treasure that I own, treasure error will get id. We're gonna assert that
that is allowed. Now let's pass some data here. Let's just change one field. So we'll
send some J S O. And

How about let's just change the value to 1, 2, 3, 4, 5. Perfect. So we can insert
status code is 200, and we can assert that the J S O n js o n matches value. 1, 2, 3,
4, 5. Excellent test. Now this should be allowed because we are the owner. So lemme
copy that method name, we'll run over and do symphony PHP bin slash PHB unit. Dash
dash filter equals our new method name and no surprises, it passes. So let's try the
other case. Let's log in as someone else and try to make a change. So I'm gonna copy
this entire browser section here. I could create another test method if I wanted to,
but this will work really well cuz right before it we can create a user two equals
user factory call and call and create one. So any other user we don't care. And we're
gonna log in as that user and then try to edit the treasure that's owned by someone
else. We'll just change the value to 67 89. In this case, this should not be allowed.
So it should be a 4 0 3 status code. When don't we try to test? Now it fails. It is
being allowed. We're getting status code 200 instead of the 4 0 3 we expected. So how
can we do this while we're in dragon treasure? No surprise, it's all gonna be about
this security attribute. Now

One thing that gets tricky here with put and patch is that you can, both of these are
used to edit users. So if you're gonna have both of them, you need to be aware of
keeping the security in sync. I'm actually going to remove put, so I can just focus
on patch. Now, this security thing here is an expression. So you can't actually get a
little fancy here we can say you have to have is granted roll, treasure, edit, and
object dot owner needs to equal user. So inside of this expression, we're given a
couple of variables. One of the variables is user, which is the current user object.
Another very important variable in here is object, which is going to be the current
object. So the dragon treasure object. So we're saying access should be allowed if
dragon treasure's owner is equal to the currently authenticated user. And now when we
try it, oh, and actually doesn't pass 500. This is where this log comes in really
handy because notice I didn't dump what the actual failure was. If I open up this
file, we can see it right here. And if this is hard to read, you can do the view page
source and beautiful. All right, it says cannot access private property, dragon
treasure owner.

And it's coming from symphony's expression language. Ah, ah, I know what I did wrong.
This is the expression language. It's not twig. So you can't do fancy things like dot
owner because owner's a property, private property, you actually need to call the
public method. Now our test works awesome. So I'm gonna get trickier here. I'm gonna
copy this test and watch this. We're gonna log in as our user and edit our own
treasure, right? All good, but this time I'm gonna try to change the owner to someone
else. User two arrow, get id. Now maybe this is something you allow. Maybe you say,
Hey, if you can edit a dragon treasure if you want to, you can assign it a different
owner. But let's pretend that's not what we want. So I'm gonna keep the assert status
4 0 3. This is gonna show us an important thing about security. When we try the test,
it fails. It actually does allow this. So spin back over and look at dragon treasure.
I wanna talk about the security key. Security is run before the new data is put onto
our object. So in other words, by the time this object here is gonna be the dragon
treasure object from the database, but before any of the new J S O N is applied to
it. So it's checking that the current owner is equal to the currently logged in user,
which is actually the main case that we want to protect. Sometimes you might also
wanna run security after the new data has been put onto the object. If you have that
use case,

You can pass something here called security post de-normalized, normalize, remember
de normalizes the word for de-normalized means it's the process of taking the data
and putting it onto the object.

So in this case, security will still run. So it's still gonna do the check for to
make sure we have that role. It's still gonna make sure the original owner is the
user. And now we can also say inside of here, object dot get owner equals user. Now
that looks identical to what we had before, but in this case, object is gonna be the
dragon treasure with the new data. So we're checking that the new owner is also equal
to the currently logged in user. By the way, if you need to, for some reason there is
also a previous object variable in here. The previous object here is equal to the
object before serialization. So it's actually the same as object up in the security
attribute and the security option, but we don't need that now when we run the test
that passes. So I do wanna kind of clarify a way of thinking here. They're kind of
two different, these two security checks are slightly different. The first security
check is trying to determine whether or not we can perform this operation at all.
Like is the current user allowed to make make a patch request? And that depends on
the current user and the current object in the database, how the current object in
the database looks. The second check here is

Saying, okay, now that I am allowed to make a patch request in general, am I allowed
to make this change to the data?

And this depends on the currently logged in user and the new version of the data. I'm
bringing this up because for me, if you're trying to figure out whether an operation
is at all allowed, regardless of what data is being sent, that is the job for
security. And this is exactly how I would implement that. However, if you determine
that the user is allowed to use an operation, but then you're trying to figure out
whether or not they're allowed to make a certain change to the data, like are they
allowed to change the owner or not? This for me is really something that's better
happen, better handled in your validation layer. So I'm gonna keep this in the
security layer right now, but later we're gonna talk about custom validation and
we're actually gonna move this check into validation and I'll kind of make this point
about the two different types of security again there later. All right, next, our
security attribute is getting a little bit complex, which I don't love. So let's
learn how we can clean this up and centralize things with a voter.


<x-mail::message>
# You've Been Invited

You've been invited to join **{{ $tenantName }}** as a team member.

<x-mail::button :url="$acceptUrl">
Accept Invitation
</x-mail::button>

This invitation link will expire in 72 hours.


Once you've set up your account, you can always sign in at:
**{{ $loginUrl }}**


If you did not expect this invitation, no action is required.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

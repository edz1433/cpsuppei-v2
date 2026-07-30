{{--
    Option list shared by the PTR's From and To end-user pickers. Custodians come
    first because selecting one widens that side of the report from a single person
    to every property moving in or out of their campus.
--}}
@if($custodians->isNotEmpty())
    <optgroup label="Campus Custodians (whole campus)">
        @foreach($custodians as $person)
            <option value="{{ $person->id }}" data-custodian="1">
                {{ $person->person_accnt }}{{ $person->campus_name ? ' — '.$person->campus_name : '' }}
            </option>
        @endforeach
    </optgroup>
@endif
<optgroup label="End Users &amp; Office Heads">
    @foreach($others as $person)
        <option value="{{ $person->id }}" data-custodian="0">
            {{ $person->person_accnt }}{{ $person->office_name ? ' — '.$person->office_name : '' }}
        </option>
    @endforeach
</optgroup>

@if(Auth::user()->id != $user->id)
    <div id="follow-form">
        @if(Auth::user()->isFollowing($user->id))
            <form action="{{ route('followers.destroy',$user->id) }}" method="post">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}

                <button type="submit" class="btn btn-primary btn-sm">取消关注</button>
            </form>
        @else
            <form action="{{ route('followers.store',$user->id) }}" method="post">
                {{ csrf_field() }}

                <button type="submit" class="btn btn-primary btn-sm">关注</button>
            </form>
        @endif
    </div>
@endif
 @include('common/header.phtml')  
<p>{$content}---haha</p>        
{loop $ar $k $v}
   <p>{$k}</p>
   <p>{if is_array($v) }{loop $v $vv} <b>{$vv}</b> {/loop}{else}<b>{$v}</b>{/if}</p>
{/loop}
 @include('common/footer.phtml')  

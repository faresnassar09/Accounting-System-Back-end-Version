<template>

<div>

    <img :src="`/storage/${$page.props.auth.user.profile_photo_path}` ?? null" 
     alt="Avatar" 
     class="rounded-full w-14 h-14" />

</div>



    
    </template>


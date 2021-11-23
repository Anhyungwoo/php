<template>
  <div id="nav">
    <router-link to="/kanban/list" v-if="$isLogin()"><i class='xi-home-o'></i></router-link>
    <router-link to="/" v-else><i class='xi-home-o'></i></router-link>
    <div class='right'>
      <router-link to="/my_info" v-if="$isLogin()"><i class='xi-user-o'></i></router-link>
      <router-link to="/join" v-else><i class="xi-user-plus"></i></router-link>
      <router-link to="/logout" v-if="$isLogin()"><i class="xi-log-out"></i></router-link>
      <router-link to="/login" v-else><i class='xi-user-o'></i></router-link>
      <router-link to="/kanban/list" v-if="$isLogin()">
        <i class='xi-view-module'></i>
      </router-link>
    </div>
  </div>
  <router-view/>
</template>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}
</style>

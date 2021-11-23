import { createRouter, createWebHistory } from 'vue-router'
import Join from '../views/member/Join.vue'
import MyInfo from '../views/member/MyInfo.vue'
import Login from '../views/member/Login.vue'
import Logout from '../views/member/Logout.vue'

import KanbanAdd from '../views/kanban/Add.vue'
import KanbanView from '../views/kanban/View.vue'
import KanbanList from '../views/kanban/List.vue'
import KanbanEdit from '../views/kanban/Edit.vue'


const routes = [
  {
    path: '/',
    name: 'Home',
    component: Login
  },
  {
    path: '/join',
    name : 'Member Join',
    component: Join
  },
  {
    path: '/login',
    name: 'Member Login',
    component: Login
  },
  {
    path: '/logout',
    name: "Member Logout",
    component: Logout
  },
  {
    path: '/my_info',
    name: "Member MyInfo",
    component: MyInfo
  },
  {
    path: '/kanban/add',
    name: "Kanban Add",
    component: KanbanAdd
  },
  {
    path: '/kanban/view',
    name: "Kanban View",
    component: KanbanView
  },
  {
    path: '/kanban/list',
    name: "Kanban List",
    component: KanbanList
  },
  {
    path: '/kanban/edit',
    name: "Kanban Edit",
    component: KanbanEdit
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router

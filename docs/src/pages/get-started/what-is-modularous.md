---
sidebarPos: 1
sidebarTitle: What is Modularous?
---

# What is Modularous?
[Unusualify/Modularous](https://github.com/unusualify/modularous) is a Laravel and Vuetify.js powered, developer tool that aims to improve developer experience on conducting full stack development process. On Laravel side, Modularous manages your large scale projects using modules, where a module similar to a single Laravel project, having some views, controllers or models. With the abilities of Vuetify.js, Modularous presents various of dynamic, configurable UI components to auto-construct a CRM for your project.

## Developer Experience

Modularous aims to provide a great Developer Experience when working on full-stack development process with:
- Presenting various custom artisan commands that undergo file generation
- Generating CRUD pages and forms based on the defined model using ability of [Vuetify.js](https://vuetifyjs.com/en/)
- Simplistic configuration or customization on the CRM panel UI through config files
- Simplistic configuration of CRUD forms through config files
  
## Organized Project Structure

Modular approach trying to resolve the complexity with a default Laravel project structure where every business logic coming together in controllers. In modular approach, each business logic is split into different parts that communicate with each other.

Every module is similar to a Laravel project, each one has its own model, views, controllers and route files.

## Dynamic & Configurable Panel UI

Powered by [Vue.js](https://vuejs.org/guide/introduction.html){target="_self"} and [Vuetify.js](https://vuetifyjs.com/){target="_self"}, your application's administration panel is auto-constructed while you developing your Laravel application.

With the abilities of Vuetify.js, Modularous presents various of dynamic, configurable UI components to auto-construct a CRM for your project.

## Used Packages
- [NWidart/Laravel-Modules](https://github.com/nWidart/laravel-modules){target="_self"} : is a Laravel package created to manage your large Laravel app using modules. A Module is like a Laravel package, it has some views, controllers or models

## For Questions and Issues

Open a GitHub issue at [unusualify/modularous](https://github.com/unusualify/modularous/issues) for bug reports, questions, or feature requests.

## Future Work

Planned improvements are tracked in the repository's GitHub Issues and Milestones. Community contributions are welcome — See [CONTRIBUTING.md](https://github.com/unusualify/modularous/blob/11.x/.github/CONTRIBUTING.md) in the repository for guidelines.

## Main Contributors

<script setup>
import { VPTeamMembers } from 'vitepress/theme'
const members = [
    {
      avatar: 'https://avatars.githubusercontent.com/u/47870922',
      name: 'Oguzhan Bukcuoglu',
      title: 'Creator / Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/OoBook' },
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/76479640',
      name: 'Erdem Çelik',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/celikerde' }
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/45737685',
      name: 'Hazarcan Doga Bakan',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/dancing-janissary' },
      ]
    },
    
    {
      avatar: 'https://avatars.githubusercontent.com/u/80110747',
      name: 'Ilker Ciblak',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/ilkerciblak' }
      ]
    },
    {
      avatar: 'https://avatars.githubusercontent.com/u/37237628',
      name: 'Gunes Bizim',
      title: 'Full Stack Developer',
      links: [
        { icon: 'github', link: 'https://github.com/gunesbizim' },
      ]
    }
  ]

</script>

<VPTeamMembers size="small" :members="members" />

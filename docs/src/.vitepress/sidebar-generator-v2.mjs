import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import matter from 'gray-matter'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const readFrontMatterSync = (fname) => {
  try {
    const readFile = fs.readFileSync(`${fname}`, 'utf-8')
    const data = matter(readFile).data
    return {
      sidebarPos: data?.sidebarPos ?? 99,
      text: data?.sidebarTitle ?? '',
    }
  } catch (error) {
    return {
      sidebarPos: 99,
    }
  }
}

const generateFileName = (fname = '') => {
  return fname.split('-').map(word => word.charAt(0).toUpperCase().concat(word.slice(1))).join(' ').replace('.md', '')
}

/** Full path for sidebar link (cleanUrls: no .md, leading slash) */
const toSidebarLink = (pathSegments) => {
  const pathStr = pathSegments.filter(Boolean).join('/').replace(/\.md$/, '')
  return pathStr ? `/${pathStr}` : '/'
}

const readLevel = (pagesDir, to) => {
  const itemList = []
  const targetPath = path.join(pagesDir, to)
  const pathParts = to.split(/[/\\]/).filter(Boolean)

  const dirs = fs.readdirSync(targetPath, { withFileTypes: true })

  dirs.forEach((dir) => {
    if (dir.isFile() && !dir.name.includes('index')) {
      const filematter = readFrontMatterSync(path.join(targetPath, dir.name))
      const link = toSidebarLink([...pathParts, dir.name])
      itemList.push({
        text: filematter?.text || generateFileName(dir.name),
        link,
        sidebarPos: filematter.sidebarPos,
      })
    } else if (dir.isDirectory()) {
      const subPath = path.join(to, dir.name)
      const subPathNorm = subPath.replace(/\\/g, '/').split('/').filter(Boolean)
      const indexPath = path.join(targetPath, dir.name, 'index.md')
      const filematter = readFrontMatterSync(indexPath)
      const childItems = readLevel(pagesDir, subPath)
      const hasIndex = fs.existsSync(indexPath)

      const overviewItem = hasIndex
        ? {
            text: filematter?.text || generateFileName(dir.name) + ' Overview',
            link: toSidebarLink(subPathNorm) + '/',
            sidebarPos: 0,
          }
        : null

      const group = {
        text: generateFileName(dir.name),
        collapsed: true,
        sidebarPos: filematter?.sidebarPos ?? 99,
        items: overviewItem
          ? [overviewItem, ...childItems].sort((a, b) => (a.sidebarPos ?? 99) - (b.sidebarPos ?? 99))
          : childItems,
      }
      itemList.push(group)
    }
  })

  itemList.sort((a, b) => (a.sidebarPos ?? 99) - (b.sidebarPos ?? 99))
  return itemList
}

export default async function(srcDir) {
  const pagesDir = srcDir || path.join(__dirname, '../pages')

  const rawDirNames = fs.readdirSync(pagesDir, { withFileTypes: true })
    .filter((d) => d.isDirectory())
    .map((d) => d.name)

  const sidebarConfig = rawDirNames.map((dir) => {
    const indexPath = path.join(pagesDir, dir, 'index.md')
    const hasIndex = fs.existsSync(indexPath)
    return {
      text: generateFileName(dir),
      collapsed: true,
      ...(hasIndex && { link: `/${dir}/` }),
      items: readLevel(pagesDir, dir),
    }
  })

  return sidebarConfig
}

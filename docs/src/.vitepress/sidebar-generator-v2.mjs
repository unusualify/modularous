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
      groupTitle: data?.sidebarGroupTitle ?? null,
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

const normalizeSidebarText = (text = '', link = '') => {
  if (!text) return text
  return text
}

const isGuideConsolePath = (pathParts = []) => pathParts[0] === 'guide' && pathParts[1] === 'console'
const isSystemReferencePath = (pathParts = []) => pathParts[0] === 'system-reference'
const useAlphaSortWithOverviewFirst = (pathParts = []) =>
  isGuideConsolePath(pathParts) || isSystemReferencePath(pathParts)

const isOverviewEntry = (item = {}) => {
  const link = item?.link ?? ''
  return link.endsWith('/overview') || link.endsWith('/')
}

const sortSidebarItems = (items = [], useAlphabeticalOrdering = false) => {
  if (!useAlphabeticalOrdering) {
    return [...items].sort((a, b) => (a.sidebarPos ?? 99) - (b.sidebarPos ?? 99))
  }

  return [...items].sort((a, b) => {
    const aOverview = isOverviewEntry(a)
    const bOverview = isOverviewEntry(b)
    if (aOverview !== bOverview) return aOverview ? -1 : 1
    return (a.text ?? '').localeCompare(b.text ?? '', undefined, { sensitivity: 'base' })
  })
}

const getOverviewLabel = (rawText = '', dirName = '', pathParts = []) => {
  const text = (rawText || '').trim()
  if (isGuideConsolePath(pathParts)) return 'Overview'
  if (isSystemReferencePath(pathParts)) return 'Overview'
  if (!text) return 'Overview'
  if (/^overview$/i.test(text)) return 'Overview'
  return text.replace(/\s+Overview$/i, '').trim() || 'Overview'
}

/** Full path for sidebar link (cleanUrls: no .md, leading slash) */
const toSidebarLink = (pathSegments) => {
  const pathStr = pathSegments.filter(Boolean).join('/').replace(/\.md$/, '')
  return pathStr ? `/${pathStr}` : '/'
}

/** Resolve the landing file for a directory. Prefer index.md (folder route), fall back to overview.md. */
const resolveLanding = (dirAbs, dirSegments) => {
  const indexPath = path.join(dirAbs, 'index.md')
  if (fs.existsSync(indexPath)) {
    return { file: indexPath, link: toSidebarLink(dirSegments) + '/', name: 'index.md' }
  }
  const overviewPath = path.join(dirAbs, 'overview.md')
  if (fs.existsSync(overviewPath)) {
    return { file: overviewPath, link: toSidebarLink([...dirSegments, 'overview']), name: 'overview.md' }
  }
  return null
}

const readLevel = (pagesDir, to) => {
  const itemList = []
  const targetPath = path.join(pagesDir, to)
  const pathParts = to.split(/[/\\]/).filter(Boolean)

  const landing = resolveLanding(targetPath, pathParts)
  const landingName = landing?.name

  const dirs = fs.readdirSync(targetPath, { withFileTypes: true })

  dirs.forEach((dir) => {
    if (dir.isFile()) {
      if (dir.name === 'index.md') return
      if (dir.name === landingName) return
      if (!dir.name.endsWith('.md')) return

      const filematter = readFrontMatterSync(path.join(targetPath, dir.name))
      const link = toSidebarLink([...pathParts, dir.name])
      itemList.push({
        text: normalizeSidebarText(filematter?.text || generateFileName(dir.name), link),
        link,
        sidebarPos: filematter.sidebarPos,
      })
    } else if (dir.isDirectory()) {
      const subPath = path.join(to, dir.name)
      const subPathNorm = subPath.replace(/\\/g, '/').split('/').filter(Boolean)
      const subLanding = resolveLanding(path.join(targetPath, dir.name), subPathNorm)
      const filematter = subLanding ? readFrontMatterSync(subLanding.file) : {}
      const childItems = readLevel(pagesDir, subPath)

      const overviewItem = subLanding
        ? {
            text: getOverviewLabel(filematter?.text || generateFileName(dir.name) + ' Overview', dir.name, subPathNorm),
            link: subLanding.link,
            sidebarPos: 0,
          }
        : null

      const group = {
        text: filematter?.groupTitle || generateFileName(dir.name),
        collapsed: true,
        sidebarPos: filematter?.sidebarPos ?? 99,
        items: overviewItem
          ? sortSidebarItems([overviewItem, ...childItems], useAlphaSortWithOverviewFirst(subPathNorm))
          : childItems,
      }
      itemList.push(group)
    }
  })

  return sortSidebarItems(itemList, useAlphaSortWithOverviewFirst(pathParts))
}

export default async function(srcDir) {
  const pagesDir = srcDir || path.join(__dirname, '../pages')

  const rawDirNames = fs.readdirSync(pagesDir, { withFileTypes: true })
    .filter((d) => d.isDirectory())
    .map((d) => d.name)

  const sidebarConfig = rawDirNames.map((dir) => {
    const landing = resolveLanding(path.join(pagesDir, dir), [dir])
    return {
      text: generateFileName(dir),
      collapsed: true,
      ...(landing && { link: landing.link }),
      items: readLevel(pagesDir, dir),
    }
  })

  return sidebarConfig
}

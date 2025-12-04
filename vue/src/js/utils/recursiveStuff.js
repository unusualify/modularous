// utils/recursiveStuff.js
import { useI18n } from 'vue-i18n';
import { cloneDeep, isArray, isObject, isEmpty, isString } from 'lodash-es';

const htmlElements = [
  // Sorted by length (descending) to ensure longer matches come first
  // 11 characters
  'Figcaption',
  // 10 characters
  'Blockquote',
  // 9 characters
  'Colgroup', 'Fieldset', 'Noscript', 'Optgroup', 'Textarea',
  // 8 characters
  'Address', 'Article', 'Caption', 'Datalist', 'Progress', 'Template',
  // 7 characters
  'Details', 'Summary', 'Section', 'Header', 'Footer', 'Legend', 'Picture', 'Portal', 'Canvas', 'Dialog', 'Object', 'Button', 'Output', 'Select', 'Iframe',
  // 6 characters
  'Figure', 'Strong', 'Source', 'Script',
  // 5 characters
  'Small', 'Title', 'Style', 'Table', 'Tbody', 'Tfoot', 'Thead', 'Track', 'Video', 'Audio', 'Input', 'Label', 'Meter', 'Aside',
  // 4 characters
  'Main', 'Menu', 'Form', 'Html', 'Base', 'Head', 'Link', 'Meta', 'Body', 'Area', 'Cite', 'Code', 'Data', 'Mark', 'Ruby', 'Samp', 'Span', 'Time', 'Abbr', 'Slot', 'Math', 'Embed',
  // 3 characters
  'Nav', 'Div', 'Pre', 'Del', 'Ins', 'Col', 'Img', 'Map', 'Svg',
  // 2 characters
  'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'Dd', 'Dl', 'Dt', 'Hr', 'Li', 'Ol', 'Br', 'Em', 'Kbd', 'Sub', 'Sup', 'Var', 'Wbr', 'Bdi', 'Bdo', 'Dfn', 'Rp', 'Rt', 'Th', 'Td', 'Tr', 'Ul',
  // 1 character
  'A', 'B', 'I', 'P', 'Q', 'S', 'U',
];
export class RecursiveStuff {
  constructor(data) {
    this.i18n = useI18n();
    this.tag = data?.tag ?? 'div'
    this.attributes = data?.attributes ?? {}
    this.elements = data?.elements ?? ''
    this.slots = data?.slots ?? {}
    this.directives = data?.directives ?? {}
  }

  setTag(tag) {
    this.tag = tag
    return this
  }

  setAttributes(attributes) {
    this.attributes = attributes
    return this
  }

  mergeAttributes(attributes) {
    this.attributes = {
      ...this.attributes,
      ...attributes
    }
    return this
  }

  setElements(elements) {
    if (elements !== '') {
      this.elements = elements;
    }

    return this
  }

  mergeElements(elements) {
    this.elements = {
      ...this.elements,
      ...elements
    }
    return this
  }

  setSlots(slots) {
    this.slots = slots
    return this
  }

  mergeSlots(slots) {
    this.slots = {
      ...this.slots,
      ...slots
    }
    return this
  }

  addSlot(slotName, slotContent) {
    this.slots[slotName] = slotContent
    return this
  }

  setDirectives(directives) {
    this.directives = directives
    return this
  }

  mergeDirectives(directives) {
    this.directives = {
      ...this.directives,
      ...directives
    }
    return this
  }

  addDirective(directive, value = true) {
    this.directives.push({
      [directive]: value
    })
    return this
  }

  addChildren(element) {
    let oldElements = cloneDeep(this.elements);

    if(!isArray(oldElements) || !isObject(oldElements)) {
      if(!isEmpty(oldElements)) {
        oldElements = [oldElements];
      } else {
        oldElements = [];
      }
    } else if(isObject(oldElements) && !isEmpty(oldElements)) {
      oldElements = [oldElements];
    }

    let newElement = [];

    if(isString(element)) {
      newElement = this.i18n.t($element);
    } else if(isArray(element)) {
      newElement = element;
    } else if(element instanceof RecursiveStuff) {
      newElement = element.render();
    } else {
      newElement = element;
    }

    if(isArray(oldElements)) {
      oldElements.push(newElement);
    }

    this.elements = cloneDeep(oldElements);

    return this;
  }

  render() {
    return {
      tag: this.tag,
      attributes: this.attributes,
      slots: this.slots,
      directives: this.directives,
      ...(this.elements ? {elements: this.elements} : {}),
    }
  }
}

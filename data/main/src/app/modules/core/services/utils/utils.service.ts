import { Injectable } from '@angular/core';
import { distinctUntilChanged, map } from 'rxjs/operators';
import { BackendQuery } from '../../interfaces/backend-query';

@Injectable({
  providedIn: 'root',
})
export class UtilsService {
  public static readonly copyPipeFn = map((data) => JSON.parse(JSON.stringify(data)));
  public static readonly distinctPipeFn = distinctUntilChanged((x, y) => JSON.stringify(x) === JSON.stringify(y));

  constructor() {}

  /**
   * Функция возвращает цвет в формате HSL
   *
   * @link https://css-tricks.com/converting-color-spaces-in-javascript/#article-header-id-9
   */
  public static hexToHsl(hex: string): [number, number, number] {
    // Convert hex to RGB first
    let [r, g, b] = this.hexToRgb(hex);

    // Then to HSL
    r /= 255;
    g /= 255;
    b /= 255;
    const colorMin = Math.min(r, g, b);
    const colorMax = Math.max(r, g, b);
    const delta = colorMax - colorMin;
    let h;

    if (delta === 0) {
      h = 0;
    } else if (colorMax === r) {
      h = ((g - b) / delta) % 6;
    } else if (colorMax === g) {
      h = (b - r) / delta + 2;
    } else {
      h = (r - g) / delta + 4;
    }

    h = Math.round(h * 60);

    if (h < 0) {
      h += 360;
    }

    let l = (colorMax + colorMin) / 2;
    let s = delta === 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));

    s = +(s * 100).toFixed(1);
    l = +(l * 100).toFixed(1);

    return [h, s, l];
  }

  /**
   * Функция возвращает цвет в формате RGB
   */
  public static hexToRgb(hex: string): [number, number, number] {
    let r;
    let g;
    let b;

    if (hex.length === 4) {
      r = '0x' + hex[1] + hex[1];
      g = '0x' + hex[2] + hex[2];
      b = '0x' + hex[3] + hex[3];
    } else if (hex.length === 7) {
      r = '0x' + hex[1] + hex[2];
      g = '0x' + hex[3] + hex[4];
      b = '0x' + hex[5] + hex[6];
    }

    r = parseInt(r, 16);
    g = parseInt(g, 16);
    b = parseInt(b, 16);

    return [r, g, b];
  }

  /**
   * Метод проверяет одинаковые ли объекты в аргументах
   * @link http://adripofjavascript.com/blog/drips/object-equality-in-javascript.html
   */
  public static isEquals(a: object, b: object): boolean {
    // Create arrays of property names
    const aProps = Object.getOwnPropertyNames(a || {});
    const bProps = Object.getOwnPropertyNames(b || {});

    // If number of properties is different, objects are not equivalent
    if (aProps.length !== bProps.length) {
      return false;
    }

    for (const propName of aProps) {
      const bothAreObjects = typeof a[propName] === 'object' && typeof b[propName] === 'object';

      if (
        (!bothAreObjects && a[propName] !== b[propName]) ||
        (bothAreObjects && !this.isEquals(a[propName], b[propName]))
      ) {
        return false;
      }
    }

    // If we made it this far, objects are considered equivalent
    return true;
  }

  /**
   * Метод проверяет, не являются ли два объекта одинаковыми методом копирования
   */
  public static isCopy(a: any, b: any): boolean {
    return JSON.stringify(a) === JSON.stringify(b);
  }

  /**
   * Метод возвращает строку, которая может быть ID HTML-элемента
   */
  public static getRandomElementId(): string {
    let text = '';
    const possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    for (let i = 0; i < 8; i++) {
      text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    text += (Math.random() * 100000).toFixed();

    return text;
  }

  /**
   * Произвольный инт в заданных пределах
   */
  public static getRandomInt(min: number, max: number): number {
    return Math.floor(Math.random() * (max - min + 1) + min);
  }

  /**
   * Функция возвращает массив с арифметической прогрессией
   * @link https://github.com/jashkenas/underscore/blob/master/underscore.js#L1699
   */
  public static range(start: number, stop: number, step: number): number[] {
    if (stop == null) {
      stop = start || 0;
      start = 0;
    }

    if (!step) {
      step = stop < start ? -1 : 1;
    }

    const length = Math.max(Math.ceil((stop - start) / step), 0);
    const range = Array(length);

    for (let idx = 0; idx < length; idx++, start += step) {
      range[idx] = start;
    }

    return range;
  }

  /**
   * Функция возвращает максимально близкое значение к нужному число из массива чисел
   * @link https://stackoverflow.com/a/8584940/6483146
   */
  public static getClosesValue(num: number, arr: number[]): number {
    let curr = arr[0];
    let diff = Math.abs(num - curr);

    // tslint:disable-next-line:prefer-for-of
    for (let val = 0; val < arr.length; val++) {
      const newDiff = Math.abs(num - arr[val]);

      if (newDiff < diff) {
        diff = newDiff;
        curr = arr[val];
      }
    }

    return curr;
  }

  /**
   * Функция возвращает угол между двумя точками
   */
  public static pointDirection(x1: number, y1: number, x2: number, y2: number): number {
    return (Math.atan2(y2 - y1, x2 - x1) * 180) / Math.PI;
  }

  /**
   * Метод рандомно перемешивает массив чего-нибудь
   */
  public static shuffle<T>(items: T[]): T[] {
    const subjectItems = JSON.parse(JSON.stringify(items));

    subjectItems.sort(() => Math.random() - Math.random());

    return subjectItems;
  }

  /**
   * Фильтр массива оставляет только уникальные значения
   */
  public static onlyUnique(value, index: number, self): boolean {
    return self.indexOf(value) === index;
  }

  /**
   * Метод возвращает копию объекта
   */
  public static copy<T>(subject: T): T {
    if (!subject) {
      return subject;
    }

    return JSON.parse(JSON.stringify(subject)) as T;
  }

  /**
   * Метод перемещает элемент в массиве с одного места на другое
   * @link https://github.com/angular/components/blob/d2d8a1f67515c0558fabde362d80de81a0b28a56/src/cdk/drag-drop/drag-utils.ts#L15
   */
  public static moveItemInArray<T = any>(array: T[], fromIndex: number, toIndex: number): void {
    const from = this.clamp(fromIndex, 0, array.length - 1);
    const to = this.clamp(toIndex, 0, array.length - 1);

    if (from === to) {
      return;
    }

    const target = array[from];
    const delta = to < from ? -1 : 1;

    for (let i = from; i !== to; i += delta) {
      array[i] = array[i + delta];
    }

    array[to] = target;
  }

  /**
   * Метод ограничивает число между нулём и максимальным значением
   * @link https://stackoverflow.com/a/11409944/6483146
   */
  public static clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
  }

  /**
   * Метод возвращает строку без html-тегов
   * @link https://stackoverflow.com/a/822486/6483146
   */
  public static stripHtml(html): string {
    const tmp = document.createElement('DIV');
    tmp.innerHTML = html;

    return tmp.textContent || tmp.innerText || '';
  }

  /**
   * Функция сортирует массив чисел объектов по ключу
   */
  public static sort<T>(arr: T[], key: keyof T, direction: 'asc' | 'desc'): void {
    const directionCorrect = direction === 'asc' ? 1 : -1;

    arr.sort((a, b) => {
      if (a[key] > b[key]) {
        return 1 * directionCorrect;
      }
      if (a[key] < b[key]) {
        return -1 * directionCorrect;
      }
      return 0;
    });
  }

  /**
   * Функция сортирует массив чисел
   * @link https://stackoverflow.com/a/1063027/6483146
   */
  public static sortNumbers(arr: number[], direction: 'asc' | 'desc'): void {
    if (direction === 'asc') {
      arr.sort((a, b) => a - b);
    } else {
      arr.sort((a, b) => b - a);
    }
  }

  /**
   * Функция сортирует массив строк в указанном порядке
   */
  public static sortStrings(arr: string[], direction: 'asc' | 'desc'): void {
    const directionCorrect = direction === 'asc' ? 1 : -1;

    arr.sort((a, b) => {
      if (a > b) {
        return 1 * directionCorrect;
      }
      if (a < b) {
        return -1 * directionCorrect;
      }
      return 0;
    });
  }

  /**
   * Метод возвращает набор полей, значения которых отличаются для двух объектов
   */
  public static getChangedFields(oldObject, newObject): Set<string> {
    const result = new Set<string>();

    const allFields = Array.from(new Set([...Object.keys(oldObject), ...Object.keys(newObject)]));

    allFields
      .filter((field) => !UtilsService.isCopy(oldObject[field], newObject[field]))
      .filter((field) => !(!oldObject[field] && !newObject[field]))
      .forEach((field) => result.add(field));

    return result;
  }

  public static getHttpParams(subject: BackendQuery): { [name: string]: string | string[] } {
    const params: { [name: string]: string | string[] } = {};

    Object.keys(subject).forEach((key) => {
      if (subject[key] === undefined) {
        params[key] = '';
      } else if (Array.isArray(subject[key])) {
        params[`${key}[]`] = subject[key];
      } else {
        params[key] = subject[key].toString();
      }
    });

    return params;
  }

  public static getIntersect(array1: any[], array2: any[]): any[] {
    return array1.filter((n) => array2.indexOf(n) !== -1);
  }

  public static getTree(dataset) {
    const hashTable = Object.create(null);
    dataset.forEach((item) => (hashTable[item.id] = { ...item, children: [] }));

    const dataTree = [];
    dataset.forEach((item) => {
      if (item.parentId) {
        hashTable[item.parentId].children.push(hashTable[item.id]);
      } else {
        dataTree.push(hashTable[item.id]);
      }
    });

    return dataTree;
  }
}

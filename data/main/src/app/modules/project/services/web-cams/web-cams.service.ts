import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { filter, map, tap } from 'rxjs/operators';
import { WebCamItem } from '../../interfaces/web-cam-item';

@Injectable({
  providedIn: 'root',
})
export class WebCamsService {
  constructor() {}

  public getList(dtId: number): Observable<WebCamItem[]> {
    return of([
      {
        id: 1,
        td: 1,
        picture: '001.png',
      },
      {
        id: 2,
        td: 1,
        picture: '002.png',
      },
      {
        id: 3,
        td: 1,
        picture: '003.png',
      },
      {
        id: 4,
        td: 1,
        picture: '004.png',
      },
      {
        id: 5,
        td: 2,
        picture: '005.png',
      },
      {
        id: 6,
        td: 2,
        picture: '006.png',
      },
      {
        id: 7,
        td: 2,
        picture: '007.png',
      },
      {
        id: 8,
        td: 2,
        picture: '008.png',
      },
      {
        id: 9,
        td: 3,
        picture: '009.png',
      },
      {
        id: 10,
        td: 3,
        picture: '010.png',
      },
      {
        id: 11,
        td: 3,
        picture: '011.png',
      },
      {
        id: 12,
        td: 3,
        picture: '012.png',
      },
      {
        id: 13,
        td: 4,
        picture: '013.png',
      },
      {
        id: 14,
        td: 4,
        picture: '014.png',
      },
      {
        id: 15,
        td: 4,
        picture: '015.png',
      },
      {
        id: 16,
        td: 4,
        picture: '016.png',
      },
      {
        id: 17,
        td: 5,
        picture: '017.png',
      },
      {
        id: 18,
        td: 5,
        picture: '018.png',
      },
      {
        id: 19,
        td: 5,
        picture: '019.png',
      },
      {
        id: 20,
        td: 5,
        picture: '020.png',
      },
    ]).pipe(map((itemList) => itemList.filter((item) => item.td === dtId)));
  }
}

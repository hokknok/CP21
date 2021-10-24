import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { TransportDetector } from '../../interfaces/transport-detector';

@Injectable({
  providedIn: 'root',
})
export class TransportDetectorService {
  private readonly selectedSubject: BehaviorSubject<number>;

  constructor() {
    this.selectedSubject = new BehaviorSubject<number>(0);
  }

  public getSelectedItem(): Observable<number> {
    return this.selectedSubject.asObservable();
  }

  public setSelectedItem(index: number): void {
    this.selectedSubject.next(index);
  }

  public getList(): Observable<TransportDetector[]> {
    return of([
      {
        id: 1,
        td: 1,
        street: 1,
        name: 'Детектор транспорта 1',
        description: 'от ул. Шумилова к ул.Фёдора Полетаева',
      },
      {
        id: 2,
        td: 1,
        street: 2,
        name: 'Детектор транспорта 2',
        description: 'от ул. Шумилова к ул. Фёдора Полетаева',
      },
      {
        id: 3,
        td: 2,
        street: 1,
        code: 'td3',
        name: 'Детектор транспорта 3',
        description: 'от ул. Жигулёвской к ул. Зеленодольская',
      },
      {
        id: 4,
        td: 2,
        street: 1,
        name: 'Детектор транспорта 4',
        description: 'от ул. Зеленодольская к ул. Жигулёвской',
      },
      {
        id: 5,
        td: 3,
        street: 1,
        name: 'Детектор транспорта 5',
        description: 'от пр-та Волгоградский к ул. Шумилова',
      },
    ]);
  }
}

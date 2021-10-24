import { Injectable } from '@angular/core';
import { Observable, ReplaySubject } from 'rxjs';
import { map } from 'rxjs/operators';
import { BackendService } from '../../../core/services/backend/backend.service';
import { GraphDataItem, GraphDataMap } from '../../interfaces/graph-data-item';

@Injectable({
  providedIn: 'root',
})
export class TransportDetectorDataService {
  private graphDataSubject: ReplaySubject<GraphDataItem>;
  private trendDataSubject: ReplaySubject<boolean>;

  constructor(private backendService: BackendService) {
    this.graphDataSubject = new ReplaySubject<GraphDataItem>(48);
    this.trendDataSubject = new ReplaySubject<boolean>(1);
  }

  public getData<T = GraphDataItem[]>(dtId: number, streetId: number, type: string): Observable<T> {
    return this.backendService.getData<T>('dt.getData', {
      id: dtId,
      street: streetId,
      type: type,
    });
  }

  public getDataMap(dtId: number, streetId: number): Observable<GraphDataMap> {
    return this.getData(dtId, streetId, 'traffic-congestion').pipe(
      map((graphData) => {
        let prepareData: GraphDataMap = {
          date: [],
          value: [],
        };

        graphData.forEach((item) => {
          prepareData.date.push(item.date);
          prepareData.value.push(item.value);
        });

        return prepareData;
      }),
    );
  }

  public getList(): Observable<GraphDataItem> {
    return this.graphDataSubject.asObservable();
  }

  public setData(item: GraphDataItem): void {
    this.graphDataSubject.next(item);
  }

  public getTrendData(): Observable<boolean> {
    return this.trendDataSubject.asObservable();
  }

  public setTrendData(item: boolean): void {
    this.trendDataSubject.next(item);
  }
}

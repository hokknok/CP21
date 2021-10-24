import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../../../environments/environment';
import { BackendQuery } from '../../interfaces/backend-query';
import { BackendResponse } from '../../interfaces/backend-response';

import { UtilsService } from '../utils/utils.service';

type BackendAction = 'dt.getData';

@Injectable({
  providedIn: 'root',
})
export class BackendService {
  constructor(private httpClient: HttpClient) {}

  public getData<T = BackendResponse>(action: BackendAction, options?: object | Partial<BackendQuery>): Observable<T> {
    const params = UtilsService.getHttpParams({
      action,
      ...options,
    });

    let endPoint = `${environment.apiUrl}/api/v1/`;

    return this.httpClient.get<T>(endPoint, { params }).pipe(UtilsService.distinctPipeFn, UtilsService.copyPipeFn);
  }

  public postData<T = BackendResponse>(path: string, options?: object | BackendQuery): Observable<T> {
    return this.httpClient.post<{ data: T }>(`/api/v1/${path}`, options).pipe(
      map((response) => {
        if (!response.data) {
          return null;
        }
        return response.data;
      }),
      UtilsService.distinctPipeFn,
      UtilsService.copyPipeFn,
    );
  }

  public sendForm<T = BackendResponse>(action: BackendAction, form: FormData): Observable<T> {
    const endpoint = `/api/v2/?action=${action}`;
    return this.httpClient.post<T>(endpoint, form);
  }
}

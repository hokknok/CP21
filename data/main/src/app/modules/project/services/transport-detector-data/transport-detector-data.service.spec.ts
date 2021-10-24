import { TestBed } from '@angular/core/testing';

import { TransportDetectorDataService } from './transport-detector-data.service';

describe('TransportDetectorDataService', () => {
  let service: TransportDetectorDataService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TransportDetectorDataService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

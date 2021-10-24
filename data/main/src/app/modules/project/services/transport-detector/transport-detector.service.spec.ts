import { TestBed } from '@angular/core/testing';

import { TransportDetectorService } from './transport-detector.service';

describe('TransportDetectorService', () => {
  let service: TransportDetectorService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(TransportDetectorService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

import { TestBed } from '@angular/core/testing';

import { WebCamsService } from './web-cams.service';

describe('WebCamsService', () => {
  let service: WebCamsService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(WebCamsService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});

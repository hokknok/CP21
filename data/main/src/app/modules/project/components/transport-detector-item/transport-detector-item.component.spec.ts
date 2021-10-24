import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TransportDetectorItemComponent } from './transport-detector-item.component';

describe('TransportDetectorItemComponent', () => {
  let component: TransportDetectorItemComponent;
  let fixture: ComponentFixture<TransportDetectorItemComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TransportDetectorItemComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TransportDetectorItemComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

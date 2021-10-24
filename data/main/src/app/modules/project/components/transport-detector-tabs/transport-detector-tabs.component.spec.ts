import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TransportDetectorTabsComponent } from './transport-detector-tabs.component';

describe('TransportDetectorTabsComponent', () => {
  let component: TransportDetectorTabsComponent;
  let fixture: ComponentFixture<TransportDetectorTabsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TransportDetectorTabsComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(TransportDetectorTabsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

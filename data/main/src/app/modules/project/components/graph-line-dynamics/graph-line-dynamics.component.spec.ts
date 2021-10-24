import { ComponentFixture, TestBed } from '@angular/core/testing';

import { GraphLineDynamicsComponent } from './graph-line-dynamics.component';

describe('GraphLineDynamicsComponent', () => {
  let component: GraphLineDynamicsComponent;
  let fixture: ComponentFixture<GraphLineDynamicsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ GraphLineDynamicsComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(GraphLineDynamicsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import React from 'react'
import Button from '../../components/Button'
import TimerControl from './TimerControl'

const TaskAction = () => {
  return ( 
        <div className="d-flex flex-wrap border-bottom pb-3 sp1_task_btn_group">
            <TimerControl />
                

            <Button 
                variant='tertiary'
                className='d-flex align-items-center btn-outline-dark mr-2 text-dark'
            >
                <i className="fa-solid fa-check"></i>
                <span className="d-inline ml-1">Mark As Complete</span>
            </Button>


            <Button 
                variant='tertiary'
                className='d-flex align-items-center btn-outline-dark mr-2 text-dark'
            >
                <i className="fa-solid fa-plus"></i>
                <span className="d-inline ml-1">Request Time Extension</span>
            </Button>
            
                

            {/* {{-- approved --}} */}
            <button type="button" className="d-flex align-items-center btn btn-sm btn-success mr-2 text-white border-0">
                <span className="d-inline mr-1">Approved</span> 
            </button> 

            {/* {{-- awaiting for time extension --}} */}
            <button type="button" className="d-flex align-items-center btn btn-sm btn-warning mr-2 text-dark border-0" >
                <span className="d-inline mr-1">Awaiting for Time Extension</span> 
            </button>

            {/* {{-- 3 dot --}} */}
            <button type="button" className="d-flex align-items-center btn btn-sm btn-outline-dark mr-2 border-0 ml-auto">
                <i className="bi bi-three-dots" ></i>
            </button>
        </div> 
  )
}

export default TaskAction
import React from 'react'
import QualifiedSalesTable from './table/QualifiedSalesTable'
import { useGetQualifiedSalesQuery, useLazyGetQualifiedSalesQuery } from '../services/api/qualifiedSalesApiSlice'
import {useUsers} from '../hooks/useUsers';
import QualifiedSalesFilterbar from './components/QualifiedSalesFilterbar';

const QualifiedSales = () => {
  const { users, usersObject, usersIsFetching } = useUsers();

  const [
    getQualifiedSales,
    {
      data,
      isFetching
    }
  ] = useLazyGetQualifiedSalesQuery();

  const handleOnFilter = (filter) => {
    let query = new URLSearchParams(filter).toString();
    getQualifiedSales(query);
  }
 

  return (
    <div className='d-flex flex-column position-relative'>
        {usersIsFetching ? 
          <div className='d-flex w-100 align-items-center justify-content-center position-absolute bg-white' style={{height: '100vh', zIndex: '1'}}>
              loading... 
          </div> 
       : null}
         <QualifiedSalesFilterbar onFilter={handleOnFilter} /> 
 
         {/*table section */}
         <div className='p-4'>
             <QualifiedSalesTable data={data || []} users={users || []} usersObject ={usersObject} isLoading={isFetching}/>
         </div>  
    </div>
  )
}

export default QualifiedSales
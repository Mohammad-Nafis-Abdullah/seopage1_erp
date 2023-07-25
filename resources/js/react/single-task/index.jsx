import React from 'react'
import ReactDOM from 'react-dom/client'
// import SingleTask from './SingleTask';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { Provider } from 'react-redux';
import {store} from '../services/store';
import Loading from './components/Loading';

const SingleTask = React.lazy(() => import('./SingleTask'));
const container = document.getElementById("sp1SingleTaskPage");

if(container){
  ReactDOM.createRoot(container).render(
    <React.StrictMode>
     <Provider store={store}>
      <BrowserRouter>
          <Routes>
              <Route path="/account/tasks/:taskId" element={
                <React.Suspense fallback={<Loading />}>
                  <SingleTask />
                </React.Suspense>
              } />
          </Routes>
      </BrowserRouter> 
     </Provider>
    </React.StrictMode>
  )
}
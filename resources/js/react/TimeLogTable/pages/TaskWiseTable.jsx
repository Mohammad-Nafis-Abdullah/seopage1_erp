import React, {useState, useEffect} from "react";
import {useGetTaskWiseDataMutation } from "../../services/api/timeLogTableApiSlice";
import { paginate } from '../../utils/paginate';
import { groupBy, orderBy } from "lodash";
import Tabbar from "../components/Tabbar";
import TaskWiseLogTable from '../components/TaskWiseLogTable'
import { TaskWiseTableColumn } from "../components/TaskWiseLogTableColumn";
import TimeLogTableFilterBar from "../components/TimeLogTableFilterBar";
import { convertTime } from "../../utils/converTime";

const TaskWiseLogReport = () => {
    const [data, setData] = useState([]);
    const [perPageData, setParPageData] = useState(10);
    const [currentPage, setCurrentPage] = useState(1);
    const [renderData, setRenderData] = useState(null);
    const [sortConfig, setSortConfig] = useState([]);
    const [trackedTime, setTrackedTime] = useState(0);

    const [getTaskWiseData, {isLoading}] = useGetTaskWiseDataMutation();

    // handle data
    const handleData = (data, currentPage, perPageData) => {
        const paginated = paginate(data, currentPage, perPageData);
        const grouped = groupBy(paginated, 'task_id');
        const sorted = Object.entries(grouped).sort(([keyA], [keyB]) => keyB - keyA);
        setRenderData([...sorted]);
    }

    // handle fetch data
    const handleFetchData = (filter) => {
        getTaskWiseData(filter)
        .unwrap()
        .then(res => {
            setCurrentPage(1);
            const sortedData = orderBy(res?.data, ["task_id"], ["desc"]);
            handleData(sortedData, 1, perPageData);
            setData(sortedData);
            const totalTrackTime = _.sumBy(sortedData, (d) => Number(d.total_minutes));
            setTrackedTime(totalTrackTime);
        })
    }
 

    // data sort handle 
    const handleSorting = (sort) => {
        const sortData = orderBy(data, ...sort);
        handleData(sortData, currentPage, perPageData);
    }

    // handle pagination
    const handlePagination = (page) => {
        setCurrentPage(page);
        handleData(data, page, perPageData);
    }

    // handle par page data change
    const handlePerPageData=(number)=>{
        setParPageData(number);
        handleData(data, currentPage, number);
    }

    return (
        <div className="sp1_tlr_container">
            <TimeLogTableFilterBar onFilter={handleFetchData} />
            <div className="sp1_tlr_tbl_container">
                <div className="mb-2"> <Tabbar/></div>
                <div className=" w-100 d-flex flex-wrap justify-center align-items-center" style={{gap: '10px'}}>
                    <span className="mx-auto">
                        Total Tracked Time: <strong>{convertTime(trackedTime)}</strong>
                    </span>
                </div>

                <TaskWiseLogTable
                    data={renderData}
                    columns={TaskWiseTableColumn}
                    tableName="task_timelog_report"
                    onSort={handleSorting}
                    height="calc(100vh - 370px)"
                    onPaginate={handlePagination}
                    perpageData={perPageData}
                    handlePerPageData={handlePerPageData}
                    currentPage={currentPage}
                    totalEntry={data.length}
                    isLoading={isLoading}
                /> 
            </div>
        </div>
    );
};

export default TaskWiseLogReport;

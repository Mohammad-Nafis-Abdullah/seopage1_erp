import React, { useState } from "react";
import Button from "../../components/Button";
import Input from "../../components/form/Input";
import TaskCategorySelectionBox from "./TaskCategorySelectionBox";
import AssginedToSelection from "./AssignedToSelection";
import StatusSelection from "./StatusSelection";
import PrioritySelection from "./PrioritySelection";
import DatePicker from "../comments/DatePicker";
import CKEditorComponent from "../../../ckeditor";
import UploadFilesInLine from "../../../file-upload/UploadFilesInLine";

import _ from "lodash";
import {
    useCreateSubtaskMutation,
    useLazyGetTaskDetailsQuery,
} from "../../../services/api/SingleTaskPageApi";
import { useParams } from "react-router-dom";

import { useDispatch, useSelector } from "react-redux";
import { setWorkingEnvironmentStatus, storeSubTasks } from "../../../services/features/subTaskSlice";
import { CompareDate } from "../../../utils/dateController";
import WorkingEnvironmentForm from "./WorkingEnvironmentForm";
import { SingleTask } from "../../../utils/single-task";
import { useEffect } from "react";
import { User } from "../../../utils/user-details";
import { Listbox } from "@headlessui/react";

const SubTaskForm = ({ close, isFirstSubtask = ture }) => {
    const { task:taskDetails, subTask, isWorkingEnvironmentSubmit } = useSelector((s) => s.subTask);
    const dispatch = useDispatch();
    const dayjs = new CompareDate();
    const [showEnvForm, setShowEnvForm] = useState(false);
    //   form data
    const [title, setTitle] = useState("");
    const [milestone, setMilestone] = useState("");
    const [parentTask, setParentTask] = useState("");
    const [startDate, setStateDate] = useState(null);
    const [dueDate, setDueDate] = useState(null);
    const [project, setProject] = useState("");
    const [taskCategory, setTaskCategory] = useState("");
    const [assignedTo, setAssignedTo] = useState("");
    const [taskObserver, setTaskObserver] = useState("");
    const [description, setDescription] = useState("");
    const [status, setStatus] = useState("To Do");
    const [priority, setPriority] = useState("Medium");
    const [estimateTimeHour, setEstimateTimeHour] = useState(0);
    const [estimateTimeMin, setEstimateTimeMin] = useState(0);
    const [files, setFiles] = React.useState([]);
    
    const [pageType, setPageType] = React.useState("");
    const [pageTypeOthers, setPageTypeOthers] = React.useState("");
    const [pageName, setPageName] = React.useState("");
    const [pageURL, setPageURL] = React.useState("");
    const [numberOfPage, setNumberOfPage] = React.useState(0);
    const [existingDesignLink, setExistingDesignLink] = React.useState("");
    const [pageTypePriority, setPageTypePriority ] = React.useState("");
    const [pageTypeName, setPageTypeName] = React.useState("");

    const task = new SingleTask(taskDetails);
    const auth = new User(window?.Laravel?.user);

    const params = useParams();
    const [createSubtask, { isLoading, error }] = useCreateSubtaskMutation();
    // const {  } = useGetTaskDetailsQuery(`/${task?.id}/json?mode=estimation_time`);
    const [ getTaskDetails, {data: estimation, isFetching}] = useLazyGetTaskDetailsQuery();

    

    const required_error = error?.status === 422 ? error?.data : null;
    // handle change
    React.useEffect(() => {
        setMilestone(task?.milestoneTitle);
        setProject(task?.projectName);
        setParentTask(task?.title);
    }, [task]);

    React.useEffect(() => {
        getTaskDetails(`/${task?.id}/json?mode=estimation_time`).unwrap();
    }, [])

    // handle onchange
    const handleChange = (e, setState) => {
        e.preventDefault();
        let value = e.target.value;
        setState(value);
    };

    // handle sumition
    const handleSubmit = (e) => {
        e.preventDefault();
        const _startDate = dayjs.dayjs(startDate).format("DD-MM-YYYY");
        const _dueDate = dayjs.dayjs(dueDate).format("DD-MM-YYYY");

        const fd = new FormData();
        fd.append("milestone_id", task?.milestoneID);
        fd.append("task_id", task?.id);
        fd.append("title", title);
        fd.append("start_date", _startDate);
        fd.append("due_date", _dueDate);
        fd.append("project_id", task?.projectId);
        fd.append("task_category_id", task?.category?.id);
        fd.append("user_id", assignedTo?.id);
        fd.append("description", description);
        fd.append("board_column_id", task?.boardColumn?.id);
        fd.append("priority", _.lowerCase(priority));
        fd.append("estimate_hours", estimateTimeHour);
        fd.append("estimate_minutes", estimateTimeMin);
        fd.append("image_url", null);
        fd.append("subTaskID", null);
        fd.append("addedFiles", null);
        fd.append('task_type', pageType ?? null);
        fd.append('page_type', pageTypePriority);
        fd.append('page_name', pageName);
        fd.append('page_url', pageURL);
        fd.append('task_type_other', pageTypeOthers);
        fd.append('page_type_name', pageTypeName);
        fd.append('number_of_pages', numberOfPage);
        fd.append('existing_design_link', existingDesignLink);
        fd.append(
            "_token",
            document
                .querySelector("meta[name='csrf-token']")
                .getAttribute("content")
        );
        Array.from(files).forEach((file) => {
            fd.append("file[]", file);
        });

        createSubtask(fd)
            .unwrap()
            .then((res) => {
                if (res?.status === "success") {
                    let _subtask = [
                        ...subTask,
                        { id: res?.sub_task?.id, title: res?.sub_task?.title },
                    ];
                    dispatch(storeSubTasks(_subtask));
                    close();

                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 2500,
                    });
                }
            })
            .catch((err) => {
                if (err?.status === 422) {
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Please fillup all required fields",
                        showConfirmButton: true,
                    });
                }
            });
    };

    React.useEffect(() => {
        if (isLoading) {
            document.getElementsByTagName("body")[0].style.cursor = "progress";
        } else {
            document.getElementsByTagName("body")[0].style.cursor = "default";
        }
    }, [isLoading]);

    // editor data handle
    const handleEditorChange = (e, editor) => {
        const data = editor.getData();
        setDescription(data);
    };

    const estimateError = (err) => {
        let errText = "";
        let hoursErr = err?.estimate_hours?.[0];
        let minErr = err?.estimate_minutes?.[0];
        if (hoursErr) errText += hoursErr;
        if (minErr) errText += minErr;
        return errText;
    };
 

    useEffect(() => {
        const showEnv = task?.workingEnvironment === 0 ? _.size(task?.subtask) === 0 ? true : false : false;
        if(auth.getRoleId() === 6){
            if(isWorkingEnvironmentSubmit === undefined){
                dispatch(setWorkingEnvironmentStatus(showEnv)) 
            }
        }
    }, [])

    return (
        <>
            <div className="sp1-subtask-form --modal-panel">
                <div className="sp1-subtask-form --modal-panel-header">
                    <h6>
                        { isWorkingEnvironmentSubmit ? "Working Environment" : "Create Sub Task"}
                    </h6>
                    <Button
                        aria-label="close-modal"
                        className="_close-modal"
                        onClick={close}
                    >
                        <i className="fa-solid fa-xmark" />
                    </Button>
                </div>

                <div className="sp1-subtask-form --modal-panel-body sp1_subtask_form">
                    {/* working environment form */}
                    {isWorkingEnvironmentSubmit && 
                        <WorkingEnvironmentForm 
                            task={task} 
                            onSubmit={() => dispatch(setWorkingEnvironmentStatus(false))}
                            close={() => setShowEnvForm(false)} 
                        /> }
                    {/* end working environment form */}

                    {!isWorkingEnvironmentSubmit && (
                        <div className="sp1-subtask-form --form row">
                            <div className="col-12 col-md-6">
                                <Input
                                    id="title"
                                    label="Title"
                                    type="text"
                                    placeholder="Enter a task title"
                                    name="title"
                                    required={true}
                                    value={title}
                                    error={required_error?.title?.[0]}
                                    onChange={(e) => handleChange(e, setTitle)}
                                />
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label
                                        className={`f-14 text-dark-gray mb-1`}
                                        data-label="true"
                                    >
                                        Milestone
                                    </label>
                                    <input
                                        className={`form-control height-35 f-14`}
                                        readOnly
                                        defaultValue={milestone}
                                    />
                                </div>
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label
                                        className={`f-14 text-dark-gray mb-1`}
                                        data-label="true"
                                    >
                                        Parent Task
                                    </label>
                                    <input
                                        className={`form-control height-35 f-14`}
                                        readOnly
                                        defaultValue={parentTask}
                                    />
                                </div>
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label
                                        className={`f-14 text-dark-gray mb-1`}
                                        data-label="true"
                                    >
                                        Project
                                    </label>
                                    <input
                                        className={`form-control height-35 f-14`}
                                        readOnly
                                        defaultValue={project}
                                    />
                                </div>
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label htmlFor="">
                                        Start Date <sup className="f-14">*</sup>
                                    </label>
                                    <div className="form-control height-35 f-14">
                                        <DatePicker
                                            placeholderText={`Ex: ${dayjs
                                                .dayjs()
                                                .format("DD-MM-YYYY")}`}
                                            minDate={dayjs.dayjs(task?.startDate).toDate()}
                                            maxDate={
                                                dueDate ||
                                                dayjs.dayjs(task?.dueDate).toDate()
                                            }
                                            date={startDate}
                                            setDate={setStateDate}
                                        />
                                    </div>
                                    {required_error?.start_date?.[0] && (
                                        <div style={{ color: "red" }}>
                                            {required_error?.start_date?.[0]}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label htmlFor="">
                                        Due Date <sup className="f-14">*</sup>
                                    </label>
                                    <div className="form-control height-35 f-14">
                                        <DatePicker
                                            placeholderText={`Ex: ${dayjs
                                                .dayjs()
                                                .format("DD-MM-YYYY")}`}
                                            minDate={
                                                startDate || dayjs.dayjs(task?.startDate).toDate()
                                            }
                                            maxDate={dayjs.dayjs(task?.dueDate).toDate()}
                                            date={dueDate}
                                            setDate={setDueDate}
                                        />
                                    </div>
                                    {required_error?.due_date?.[0] && (
                                        <div style={{ color: "red" }}>
                                            {required_error?.due_date?.[0]}
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="col-12 col-md-6">
                                <TaskCategorySelectionBox
                                    selected={taskCategory}
                                    onSelect={setTaskCategory}
                                />
                            </div>

                            <div className="col-12 col-md-6">
                                <AssginedToSelection
                                    selected={assignedTo}
                                    onSelect={setAssignedTo}
                                />
                            </div>
                            {/* 
                    <div className="col-6">
                        <TaskObserverSelection />
                    </div> */}

                            {/* <div className="col-12 col-md-6">
                                <StatusSelection />
                            </div> */}

                            {/* Page Type  */}
                            <div className="col-12 col-md-6">
                                <Listbox value={pageType} onChange={setPageType}>
                                    <div className="form-group position-relative my-3">
                                        <label htmlFor=""> Task Type <sup>*</sup> </label>
                                            <Listbox.Button className=" sp1-selection-display-button form-control height-35 f-14 sp1-selection-display bg-white w-100"> 
                                            <span className="singleline-ellipsis pr-3">
                                                {pageType ?? "--"}
                                            </span>
                                            
                                            <div className='__icon'>
                                                <i className="fa-solid fa-sort"></i>
                                            </div>
                                        </Listbox.Button>
                                        <Listbox.Options  className="sp1-select-options">
                                            {["New Page Design", "Cloning Existing Desgin", "Others"]?.map((s, i) => (
                                                <Listbox.Option 
                                                    key={i}
                                                    className={({ active }) =>  `sp1-select-option ${ active ? 'active' : ''}`}
                                                    value={s}
                                                > 
                                                    {({selected}) => (
                                                        <>
                                                          {s}     

                                                          {selected ? <i className="fa-solid fa-check ml-2" />: ''}
                                                        </>
                                                    )}
                                                </Listbox.Option>
                                            ))}
                                        </Listbox.Options>
                                    </div>
                                </Listbox>
                            </div>

                            {
                                pageType === "New Page Design"? 
                                <div className="col-12 col-md-6">
                                    <Listbox value={pageTypePriority} onChange={setPageTypePriority}>
                                        <div className="form-group position-relative my-3">
                                            <label htmlFor=""> Page Type <sup>*</sup> </label>
                                                <Listbox.Button className=" sp1-selection-display-button form-control height-35 f-14 sp1-selection-display bg-white w-100"> 
                                                <span className="singleline-ellipsis pr-3">
                                                    {pageTypePriority ?? "--"}
                                                </span>
                                                
                                                <div className='__icon'>
                                                    <i className="fa-solid fa-sort"></i>
                                                </div>
                                            </Listbox.Button>
                                            <Listbox.Options  className="sp1-select-options">
                                                {[
                                                    "Primary Page Development",
                                                    "Secondary Page Development",
                                                ]?.map((s, i) => (
                                                    <Listbox.Option 
                                                        key={i}
                                                        className={({ active }) =>  `sp1-select-option ${ active ? 'active' : ''}`}
                                                        value={s}
                                                    >
                                                        {({selected}) => (
                                                            <>
                                                            {s}     

                                                            {selected ? <i className="fa-solid fa-check ml-2" />: ''}
                                                            </>
                                                        )}
                                                        
                                                    </Listbox.Option>
                                                ))}
                                            </Listbox.Options>
                                        </div>
                                    </Listbox>
                                </div> : null
                            }

                            {/* Others */}
                            {
                                pageType === "Others"? 
                                <div className="col-12 col-md-6">
                                    <Listbox value={pageTypeOthers} onChange={setPageTypeOthers}>
                                        <div className="form-group position-relative my-3">
                                            <label htmlFor=""> Others <sup>*</sup> </label>
                                                <Listbox.Button className=" sp1-selection-display-button form-control height-35 f-14 sp1-selection-display bg-white w-100"> 
                                                <span className="singleline-ellipsis pr-3">
                                                    {pageTypeOthers ?? "--"}
                                                </span>
                                                
                                                <div className='__icon'>
                                                    <i className="fa-solid fa-sort"></i>
                                                </div>
                                            </Listbox.Button>
                                            <Listbox.Options  className="sp1-select-options">
                                                {[
                                                    "Page Design Change",
                                                    "Speed Optimization",
                                                    "Fixing Issues/Bugs",
                                                    "Responsiveness Issue Fixing/Making Something Responsive"
                                                ]?.map((s, i) => (
                                                    <Listbox.Option 
                                                        key={i}
                                                        className={({ active }) =>  `sp1-select-option ${ active ? 'active' : ''}`}
                                                        value={s}
                                                    >
                                                        {({selected}) => (
                                                            <>
                                                            {s}     

                                                            {selected ? <i className="fa-solid fa-check ml-2" />: ''}
                                                            </>
                                                        )}
                                                        
                                                    </Listbox.Option>
                                                ))}
                                            </Listbox.Options>
                                        </div>
                                    </Listbox>
                                </div> : null
                            }

                           {
                             pageType ? 
                                <React.Fragment>
                                    {
                                        pageType === "Cloning Existing Desgin" ? 
                                            <div className="col-12 col-md-6">
                                                <Input
                                                    id="page_type_name"
                                                    label="Page type name"
                                                    type="text"
                                                    placeholder="Enter page type name..."
                                                    name="pageTypeName"
                                                    required={true}
                                                    value={pageTypeName}
                                                    error={required_error?.title?.[0]}
                                                    onChange={(e) => handleChange(e, setPageTypeName)}
                                                />
                                            </div>: 
                                            <>
                                                <div className="col-12 col-md-6">
                                                    <Input
                                                        id="page_name"
                                                        label="Page Name"
                                                        type="text"
                                                        placeholder="Enter page name"
                                                        name="page name"
                                                        required={true}
                                                        value={pageName}
                                                        error={required_error?.title?.[0]}
                                                        onChange={(e) => handleChange(e, setPageName)}
                                                    />
                                                </div>

                                                
                                                <div className="col-12 col-md-6">
                                                    <Input
                                                        id="page_url"
                                                        label="Page URL"
                                                        type="text"
                                                        placeholder="Enter page url"
                                                        name="page url"
                                                        required={true}
                                                        value={pageURL}
                                                        error={required_error?.title?.[0]}
                                                        onChange={(e) => handleChange(e, setPageURL)}
                                                    />
                                                </div>
                                            </>
                                    }

                                    


                                    {
                                        pageType=== "Cloning Existing Desgin"?
                                        <>
                                            <div className="col-12 col-md-6">
                                                <Input
                                                    id="number_of_pages"
                                                    label="Number of Pages"
                                                    type="number"
                                                    placeholder="--"
                                                    name="number_of_pages"
                                                    required={true}
                                                    value={numberOfPage}
                                                    error={required_error?.title?.[0]}
                                                    onChange={(e) => handleChange(e, setNumberOfPage)}
                                                />
                                            </div>

                                            <div className="col-12 col-md-6">
                                                <Input
                                                    id="exiting_project_url"
                                                    label="Existing Design Link"
                                                    type="Link"
                                                    placeholder="--"
                                                    name="exiting_project_url"
                                                    required={true}
                                                    value={existingDesignLink}
                                                    error={required_error?.title?.[0]}
                                                    onChange={(e) => handleChange(e, setExistingDesignLink)}
                                                />
                                            </div>
                                        </> : null
                                    }
                                </React.Fragment>
                             :null
                           }
                            {/*  */}

                            

                            <div className="col-12 col-md-6">
                                <PrioritySelection
                                    selected={priority}
                                    setSelected={setPriority}
                                />
                            </div>

                            <div className="col-12 col-md-6">
                                <div className="form-group my-3">
                                    <label
                                        htmlFor=""
                                        className="f-14 text-dark-gray"
                                    >
                                        Set Estimate Time{" "}
                                        <sup className="f-14"> * </sup>
                                    </label>
                                    <div className="d-flex align-items-center">
                                        <input
                                            type="Number"
                                            className="form-control height-35 f-14 mr-2"
                                            value={estimateTimeHour}
                                            onChange={(e) =>handleChange( e,setEstimateTimeHour)}
                                        />{" "}
                                        hrs
                                        <input
                                            type="Number"
                                            className="form-control height-35 f-14 mr-2 ml-2"
                                            value={estimateTimeMin}
                                            onChange={(e) =>
                                                handleChange(
                                                    e,
                                                    setEstimateTimeMin
                                                )
                                            }
                                        />{" "}
                                        min
                                    </div>

                                    <div style={{ color: "red" }}>
                                        {estimateError(required_error)}
                                    </div>
                                    <div style={{ color: "red" }}>
                                    Estimation time can't exceed {estimation?.hours_left} hours {estimation?.minutes_left} minutes
                                    </div>
                                </div>
                            </div>

                            <div className="col-12">
                                <div className="form-group my-3">
                                    <label htmlFor=""> Description </label>
                                    <div
                                        className="sp1_st_write_comment_editor"
                                        style={{ minHeight: "100px" }}
                                    >
                                        <CKEditorComponent
                                            onChange={handleEditorChange}
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="col-12">
                                <UploadFilesInLine
                                    files={files}
                                    setFiles={setFiles}
                                />
                            </div>

                            <div className="col-12 mt-3 pb-3">
                                <div className="d-flex align-items-center justify-content-end">
                                    <Button
                                        variant="secondary"
                                        className="mr-2"
                                        onClick={close}
                                    >
                                        Cancel
                                    </Button>

                                    {!isLoading ? (
                                        <Button onClick={handleSubmit}>
                                            <i className="fa-regular fa-paper-plane"></i>
                                            Create
                                        </Button>
                                    ) : (
                                        <Button className="cursor-processing">
                                            <div
                                                className="spinner-border text-white"
                                                role="status"
                                                style={{
                                                    width: "18px",
                                                    height: "18px",
                                                }}
                                            ></div>
                                            Processing...
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </>
    );
};

export default SubTaskForm;
